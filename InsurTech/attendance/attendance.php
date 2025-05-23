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

// Get current attendance status
$check_query = mysqli_query($conn, "SELECT * FROM employee_attendance WHERE employee_id = '$employee_id' AND date = '$current_date'");
$attendance = mysqli_fetch_assoc($check_query);
$check_in_timestamp = null;

if ($attendance && is_null($attendance['check_out_time'])) {
    // Convert check_in_time to a timestamp for JavaScript
    $check_in_timestamp = strtotime($current_date . ' ' . $attendance['check_in_time']);
}
?>

<li class="d-flex align-items-center flex-wrap">
    <?php 
    if ($employee_id) {
        if ($ip_restricted == 1 && !$ip_allowed) {
            echo "<span class='text-danger pr-2 fw-bold'>IP Restricted. Contact Admin.</span>";
        } else {
            if ($attendance && is_null($attendance['check_out_time'])) { ?>
                <div class="d-flex align-items-center me-3">
                    <div class="clock-container">
                        <div class="clock-header">
                            <i class="fas fa-clock me-2"></i>
                            <span>Check In : <?php echo date("H:i:s A", strtotime($attendance['check_in_time'])); ?></span>
                        </div>
                        <div id="timer" class="clock-display" data-checkin="<?php echo strtotime($attendance['check_in_time']);?>">
                            <div class="time-unit">
                                <span id="hours">00</span>
                                <!-- <label>HRS</label> -->
                            </div>
                            <div class="time-divider">:</div>
                            <div class="time-unit">
                                <span id="minutes">00</span>
                                <!-- <label>MIN</label> -->
                            </div>
                            <div class="time-divider">:</div>
                            <div class="time-unit">
                                <span id="seconds">00</span>
                                <!-- <label>SEC</label> -->
                            </div>
                        </div>
                    </div>
                </div>
                <button class="btn btn-primary me-3 text-white" id="checkOutBtn">Check - Out</button>
            <?php 
            } elseif (!$attendance) { ?>
                <button class="btn btn-primary me-3 text-white" id="checkInBtn">Check - In</button>
            <?php }
        }
    } else {
        echo "<span class='text-danger'>Please login to mark attendance.</span>";
    }
    ?>
</li>

<!-- Video Modal -->
<div class="modal" id="videoModal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <video id="video" autoplay></video>
        <div id="modal-message"></div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function () {
    let stream = null;
    const irisDetectionEnabled = <?php echo $iris_detection; ?>;
    let timerInterval;
    
    // Initialize and start timer if check-in time exists
    initializeTimer();
    
    function initializeTimer() {
        const timerElement = document.getElementById('timer');
        if (!timerElement) return;
        
        const checkInTimestamp = parseInt(timerElement.getAttribute('data-checkin'));
        if (!checkInTimestamp) return;
        
        // Update timer immediately
        updateTimerDisplay(checkInTimestamp);
        
        // Update timer every second
        timerInterval = setInterval(function() {
            updateTimerDisplay(checkInTimestamp);
        }, 1000);
    }
    
    function updateTimerDisplay(checkInTimestamp) {
        const now = Math.floor(Date.now() / 1000); // Current timestamp in seconds
        const elapsed = now - checkInTimestamp; // elapsed time in seconds
        
        if (isNaN(elapsed) || elapsed < 0) {
            console.error("Invalid elapsed time calculation:", elapsed);
            return;
        }

        const hours = Math.floor(elapsed / 3600);
        const minutes = Math.floor((elapsed % 3600) / 60);
        const seconds = elapsed % 60;
        
        document.getElementById('hours').textContent = hours.toString().padStart(2, '0');
        document.getElementById('minutes').textContent = minutes.toString().padStart(2, '0');
        document.getElementById('seconds').textContent = seconds.toString().padStart(2, '0');
    }
    
    // Initialize location tracking
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
    
    // Get location when the page loads
    getLocation(function(locationEnabled) {
        if (!locationEnabled) return;
        
        // Event handlers for check-in and check-out buttons
        $('#checkInBtn').on('click', function() {
            if (confirm("Are you sure you want to Check-In?")) {
                handleAttendance(true); // true means Check-In
            }
        });
        
        $('#checkOutBtn').on('click', function() {
            if (confirm("Are you sure you want to Check-Out?")) {
                handleAttendance(false); // false means Check-Out
            }
        });
    });
    
    // Main attendance handling function
    function handleAttendance(isCheckin) {
        // If iris detection is not enabled, directly submit attendance
        if (!irisDetectionEnabled) {
            submitAttendance(isCheckin);
            return;
        }
        
        // Otherwise open camera modal
        openModal(isCheckin);
    }
    
    // Function to submit attendance without iris verification
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
        
        $.ajax({
            url: `attendance/${isCheckin ? 'checkin' : 'checkout'}.php`,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                alert(response);
                location.reload();
            },
            error: function(xhr, status, error) {
                console.error("Attendance submission error:", error);
                alert("Error submitting attendance: " + error);
            }
        });
    }

    // Function to open camera modal
    function openModal(isCheckin) {
        const modal = $('#videoModal');
        const video = document.getElementById('video');
        const modalMessage = $('#modal-message');
        
        // Clear previous messages
        modalMessage.html('<p>Initializing camera for iris verification...</p>');
        
        modal.show();

        // Request camera access
        navigator.mediaDevices.getUserMedia({ video: true })
        .then(function(s) {
            stream = s;
            video.srcObject = stream;
            
            // Wait for video to fully load and stabilize
            video.onloadeddata = function() {
                setTimeout(() => {
                    if (video.readyState >= 2) {
                        modalMessage.html('<p>Capturing iris image...</p>');
                        autoCaptureAndVerify(isCheckin);
                    } else {
                        modalMessage.html('<p>Camera initializing. Please wait...</p>');
                        setTimeout(() => autoCaptureAndVerify(isCheckin), 1000);
                    }
                }, 1500);
            };
        })
        .catch(function(err) {
            modalMessage.html('<p class="text-danger">Camera access denied: ' + err.message + '</p>');
            modalMessage.append('<button id="closeBtn" class="btn btn-danger mt-2">Close</button>');
        });
    }
    
    // Function to auto-capture and verify iris
    function autoCaptureAndVerify(isCheckin) {
        const video = document.getElementById('video');
        const modalMessage = $('#modal-message');
        
        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth || 640;
        canvas.height = video.videoHeight || 480;

        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        canvas.toBlob(function(blob) {
            const reader = new FileReader();
            reader.onloadend = function() {
                const base64data = reader.result.split(',')[1];

                // Get location from sessionStorage
                const latitude = sessionStorage.getItem('latitude');
                const longitude = sessionStorage.getItem('longitude');

                if (!latitude || !longitude) {
                    modalMessage.html('<p class="text-danger">Location data is missing. Please allow location access and try again.</p>');
                    appendRetryCloseButtons();
                    return;
                }

                const employee_id = "<?php echo $_SESSION['USEROID'] ?? ''; ?>";
                modalMessage.html('<p>Verifying iris pattern...</p>');

                // AJAX call to get stored iris data
                $.ajax({
                    url: 'attendance/get_iris_data.php',
                    type: 'POST',
                    data: { employee_id: employee_id },
                    success: function(response) {
                        try {
                            const irisData = JSON.parse(response);

                            if (irisData.status === 'error') {
                                modalMessage.html('<p class="text-danger">Error fetching stored data: ' + irisData.message + '</p>');
                                appendRetryCloseButtons();
                                return;
                            }

                            // Send both the live image and stored data to the API
                            const payload = {
                                image: "data:image/jpeg;base64," + base64data,
                                stored_iris_data: irisData.iris_data
                            };

                            // Use $.ajax instead of fetch for better compatibility
                            $.ajax({
                                url: 'http://127.0.0.1:5000/verify_with_stored_data',
                                method: 'POST',
                                data: JSON.stringify(payload),
                                contentType: 'application/json',
                                success: function(data) {
                                    console.log("Server Response:", data);

                                    if (data.status === 'success') {
                                        modalMessage.html('<p class="text-success fw-bold">✅ Identity Verified: ' + data.message + '</p>');
                                        
                                        // Submit attendance
                                        const formData = new FormData();
                                        formData.append('latitude', latitude);
                                        formData.append('longitude', longitude);

                                        $.ajax({
                                            url: `attendance/${isCheckin ? 'checkin' : 'checkout'}.php`,
                                            method: 'POST',
                                            data: formData,
                                            processData: false,
                                            contentType: false,
                                            success: function(msg) {
                                                modalMessage.append('<p class="text-success">' + msg + '</p>');
                                                modalMessage.append('<p>Page will reload in 3 seconds...</p>');
                                                setTimeout(() => {
                                                    location.reload();
                                                }, 3000);
                                            },
                                            error: function(xhr, status, error) {
                                                console.error("Attendance submission error:", error);
                                                modalMessage.append('<p class="text-danger">Error submitting attendance: ' + error + '</p>');
                                                appendRetryCloseButtons();
                                            }
                                        });
                                    } else {
                                        modalMessage.html('<p class="text-danger mt-3 fw-bold">' + data.message + '</p>');
                                        appendRetryCloseButtons();
                                    }
                                },
                                error: function(xhr, status, error) {
                                    console.error("API Error:", error);
                                    modalMessage.html('<p class="text-danger mt-3 fw-bold">API Error: ' + error + '</p>');
                                    appendRetryCloseButtons();
                                }
                            });
                        } catch (e) {
                            modalMessage.html('<p class="text-danger">Error parsing iris data: ' + e.message + '</p>');
                            appendRetryCloseButtons();
                        }
                    },
                    error: function(xhr, status, error) {
                        modalMessage.html('<p class="text-danger">Failed to fetch iris data: ' + error + '</p>');
                        appendRetryCloseButtons();
                    }
                });
            };
            reader.readAsDataURL(blob);
        });
    }
    
    // Function to append retry and close buttons
    function appendRetryCloseButtons() {
        const retryBtn = $('<button id="retryBtn" class="btn btn-warning mt-2 me-2">Retry</button>');
        const closeBtn = $('<button id="closeBtn" class="btn btn-danger mt-2">Close</button>');
        $('#modal-message').append(retryBtn).append(closeBtn);
    }
    
    // Event handlers using document delegation
    $(document).on('click', '#retryBtn', function() {
        $('#modal-message').html('<p>Restarting iris verification...</p>');
        setTimeout(() => autoCaptureAndVerify($(this).data('isCheckin')), 1000);
    });
    
    $(document).on('click', '#closeBtn, .close', function() {
        closeModal();
    });
    
    // Function to close modal
    function closeModal() {
        $('#videoModal').hide();
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
        $('#modal-message').html('');
    }
    
    // Clear timer interval when page is unloaded
    $(window).on('beforeunload', function() {
        if (timerInterval) {
            clearInterval(timerInterval);
        }
    });
});
</script>