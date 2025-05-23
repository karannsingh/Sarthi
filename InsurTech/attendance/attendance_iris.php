<?php
// Get user IP
function getUserIP() {
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipList = explode(",", $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ipList[0]);
    } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

$ip = getUserIP();
$employee_id = $_SESSION['USEROID'] ?? null;
$current_date = date("Y-m-d");

// Check if employee exists and get their data
$user_query = mysqli_query($conn, "SELECT * FROM users WHERE UserOID = '$employee_id'");
$user_data = mysqli_fetch_assoc($user_query);

if (!$user_data) {
    echo "<script>alert('User not found. Please login again.');</script>";
    exit;
}

// Get IP restriction and iris detection settings
$ip_restricted = $user_data['IPRestricted'] ?? 0;
$iris_detection = $user_data['IrisDetection'] ?? 0;

// Check IP if restriction is enabled
$ip_allowed = true;
if ($ip_restricted == 1) {
    // Query to check if IP is in allowed list
    $ip_query = mysqli_query($conn, "SELECT * FROM allowedip WHERE is_active = 1 AND is_deleted = 0 AND 
                                    (ip_address = '$ip' OR 
                                    (prefix_ip != '' AND prefix_ip IS NOT NULL AND '$ip' LIKE CONCAT(prefix_ip, '%')))");
    $ip_allowed = mysqli_num_rows($ip_query) > 0;
}

// Fetch employee shift details
$shift_query = mysqli_query($conn, "SELECT * FROM employee_shifts WHERE employee_id = '$employee_id'");
$shift_data = mysqli_fetch_assoc($shift_query);

// Ensure shift exists
if (!$shift_data) {
    echo "<script>alert('Shift details not found. Please contact admin.');</script>";
    exit;
}

$shift_start = strtotime($current_date . " " . $shift_data['shift_start']);
$shift_end = strtotime($current_date . " " . $shift_data['shift_end']);
$late_cutoff = strtotime($current_date . " " . $shift_data['late_cutoff']);
$current_time = strtotime(date("H:i:s"));
?>

<li class="d-flex align-items-center">
    <?php 
    if ($employee_id) {
        if ($ip_restricted == 1 && !$ip_allowed) {
            echo "<span class='text-danger'>IP restriction enabled. Your current IP ($ip) is not allowed.</span>";
        } else {
            // Check current attendance status
            $check = mysqli_query($conn, "SELECT * FROM employee_attendance WHERE employee_id = '$employee_id' AND date = '$current_date'");
            $row = mysqli_fetch_assoc($check);

            if ($row && is_null($row['check_out_time'])) { ?>
                <button class="btn btn-primary me-3 text-white" id="checkOutBtn">Check - Out</button>
            <?php 
            } elseif (!$row) { ?>
                <button class="btn btn-primary me-3 text-white" id="checkInBtn">Check - In</button>
            <?php }
        }
    } else {
        echo "<span class='text-danger'>Please login to mark attendance.</span>";
    }
    ?>
</li>

<!-- Video Modal -->
<div class="modal" id="videoModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <video id="video" autoplay></video>
        <div id="modal-message"></div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function () {
    $('#checkInBtn').on('click', function () {
        if (confirm("Are you sure you want to Check-In?")) {
            handleAttendance(true); // true means Check-In
        } else {
            console.log("Check-In canceled.");
        }
    });

    $('#checkOutBtn').on('click', function () {
        if (confirm("Are you sure you want to Check-Out?")) {
            handleAttendance(false); // false means Check-Out
        } else {
            console.log("Check-Out canceled.");
        }
    });
    let stream = null;
    const irisDetectionEnabled = <?php echo $iris_detection; ?>;
    
    function getLocation(callback) {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function (position) {
                    sessionStorage.setItem('latitude', position.coords.latitude);
                    sessionStorage.setItem('longitude', position.coords.longitude);
                    callback(true);
                },
                function () {
                    alert('Location Access Denied. Attendance disabled.');
                    $('button').prop('disabled', true);
                    callback(false);
                }
            );
        } else {
            alert('Geolocation is not supported.');
            $('button').prop('disabled', true);
            callback(false);
        }
    }

    function handleAttendance(isCheckin) {
        // If iris detection is not enabled, directly submit attendance
        if (!irisDetectionEnabled) {
            submitAttendance(isCheckin);
            return;
        }
        
        // Otherwise open camera modal
        openModal(isCheckin);
    }
    
    function submitAttendance(isCheckin) {
        const latitude = sessionStorage.getItem('latitude');
        const longitude = sessionStorage.getItem('longitude');
        
        if (!latitude || !longitude) {
            alert('Location data is missing. Please allow location access and try again.');
            return;
        }
        
        const formData = new FormData();
        formData.append('latitude', latitude);
        formData.append('longitude', longitude);
        
        fetch(`attendance/${isCheckin ? 'checkin' : 'checkout'}.php`, {
            method: 'POST',
            body: formData
        })
        .then(res => res.text())
        .then(msg => {
            alert(msg);
            location.reload();
        })
        .catch(error => {
            console.error("Attendance submission error:", error);
            alert("Error submitting attendance: " + error);
        });
    }

    function openModal(isCheckin) {
        const modal = $('#videoModal');
        const video = document.getElementById('video');
        const modalMessage = $('#modal-message');
        
        // Clear previous messages
        modalMessage.html('');
        modalMessage.append('<p>Initializing camera for iris verification...</p>');
        
        modal.show();

        navigator.mediaDevices.getUserMedia({ video: true }).then(function (s) {
            stream = s;
            video.srcObject = stream;
            modalMessage.html('<p>Camera activated. Preparing for iris scan...</p>');

            // Wait for video to fully load and stabilize
            video.onloadeddata = function () {
                console.log("Video loaded.");
                setTimeout(() => {
                    if (video.readyState >= 2) {
                        modalMessage.html('<p>Capturing iris image...</p>');
                        autoCaptureAndVerify(isCheckin);
                    } else {
                        console.log("Video not ready. Retrying in 1s...");
                        modalMessage.html('<p>Camera initializing. Please wait...</p>');
                        setTimeout(() => autoCaptureAndVerify(isCheckin), 1000);
                    }
                }, 1500); // wait 1.5 seconds after video loads
            };
        }).catch(function (err) {
            modalMessage.html('<p class="text-danger">Camera access denied: ' + err.message + '</p>');
            const closeBtn = $('<button id="closeBtn" class="btn btn-danger mt-2">Close</button>');
            modalMessage.append(closeBtn);
            
            // Close button event
            $(document).on('click', '#closeBtn', function() {
                closeModal();
            });
        });

        function autoCaptureAndVerify(isCheckin) {
            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth || 640;
            canvas.height = video.videoHeight || 480;

            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            canvas.toBlob(function (blob) {
                const reader = new FileReader();
                reader.onloadend = function () {
                    const base64data = reader.result.split(',')[1];

                    // Get location from sessionStorage
                    const latitude = sessionStorage.getItem('latitude');
                    const longitude = sessionStorage.getItem('longitude');

                    if (!latitude || !longitude) {
                        modalMessage.html('<p class="text-danger">Location data is missing. Please allow location access and try again.</p>');
                        appendRetryCloseButtons();
                        return;
                    }

                    const employee_id = "<?php echo $_SESSION['USEROID']; ?>";
                    modalMessage.html('<p>Verifying iris pattern...</p>');

                    // AJAX call to get stored iris data
                    $.ajax({
                        url: 'attendance/get_iris_data.php',
                        type: 'POST',
                        data: { employee_id: employee_id },
                        success: function (response) {
                            try {
                                const irisData = JSON.parse(response);

                                if (irisData.status === 'error') {
                                    modalMessage.html('<p class="text-danger">Error fetching stored data: ' + irisData.message + '</p>');
                                    appendRetryCloseButtons();
                                    return;
                                }

                                // Now send both the live image and stored data to the API
                                const payload = {
                                    image: "data:image/jpeg;base64," + base64data,
                                    stored_iris_data: irisData.iris_data
                                };

                                fetch('http://127.0.0.1:5000/verify_with_stored_data', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json'
                                    },
                                    body: JSON.stringify(payload)
                                })
                                .then(res => res.json())
                                .then(data => {
                                    console.log("Server Response:", data);

                                    if (data.status === 'success') {
                                        modalMessage.html('<p class="text-success fw-bold">✅ Identity Verified: ' + data.message + '</p>');
                                        
                                        // Submit location and mark attendance
                                        const formData = new FormData();
                                        formData.append('latitude', latitude);
                                        formData.append('longitude', longitude);

                                        fetch(`attendance/${isCheckin ? 'checkin' : 'checkout'}.php`, {
                                            method: 'POST',
                                            body: formData
                                        })
                                        .then(res => res.text())
                                        .then(msg => {
                                            modalMessage.append('<p class="text-success">' + msg + '</p>');
                                            modalMessage.append('<p>Page will reload in 3 seconds...</p>');
                                            setTimeout(() => {
                                                location.reload();
                                            }, 3000);
                                        })
                                        .catch(error => {
                                            console.error("Attendance submission error:", error);
                                            modalMessage.append('<p class="text-danger">Error submitting attendance: ' + error + '</p>');
                                            appendRetryCloseButtons();
                                        });
                                    } else {
                                        modalMessage.html('<p class="text-danger mt-3 fw-bold">' + data.message + '</p>');
                                        appendRetryCloseButtons();
                                    }
                                })
                                .catch(error => {
                                    console.error("API Error:", error);
                                    modalMessage.html('<p class="text-danger mt-3 fw-bold">API Error: ' + error.message + '</p>');
                                    appendRetryCloseButtons();
                                });

                            } catch (e) {
                                modalMessage.html('<p class="text-danger">Error parsing iris data: ' + e.message + '</p>');
                                appendRetryCloseButtons();
                            }
                        },
                        error: function (xhr, status, error) {
                            modalMessage.html('<p class="text-danger">Failed to fetch iris data: ' + error + '</p>');
                            appendRetryCloseButtons();
                        }
                    });
                };
                reader.readAsDataURL(blob);
            });
        }

        function appendRetryCloseButtons() {
            const retryBtn = $('<button id="retryBtn" class="btn btn-warning mt-2 me-2">Retry</button>');
            const closeBtn = $('<button id="closeBtn" class="btn btn-danger mt-2">Close</button>');
            modalMessage.append(retryBtn).append(closeBtn);
        }

        $(document).on('click', '#retryBtn', function () {
            modalMessage.html('<p>Restarting iris verification...</p>');
            setTimeout(() => autoCaptureAndVerify(isCheckin), 1000);
        });

        $(document).on('click', '#closeBtn, .close', function () {
            closeModal();
        });
    }
    
    function closeModal() {
        $('#videoModal').hide();
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
        $('#modal-message').html('');
    }

    getLocation(function (enabled) {
        if (!enabled) return;

        $('#checkInBtn').click(function () {
            handleAttendance(true);
        });
        $('#checkOutBtn').click(function () {
            handleAttendance(false);
        });
    });
});
</script>
<style>
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.6);
        z-index: 1050;
    }
    .modal-content {
        background-color: #fff;
        margin: 15% auto;
        padding: 20px;
        width: 60%;
        border-radius: 8px;
        text-align: center;
        position: relative;
    }
    video {
        width: 100%;
        max-width: 640px;
        border-radius: 8px;
        margin-bottom: 15px;
    }
    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }
    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
    }
    #modal-message {
        margin-top: 10px;
    }
    .text-success {
        color: #28a745;
    }
    .text-danger {
        color: #dc3545;
    }
    .fw-bold {
        font-weight: bold;
    }
</style>