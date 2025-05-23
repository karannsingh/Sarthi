<?php
// Get user IP
function getUserIP() {
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipList = explode(",", $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ipList[0]); // Get first IP if multiple exist
    } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

$ip = getUserIP();
$allowed_ip_prefix = "154.84."; // Allow all IPs starting with this
$allowed_local_ips = ["::1"]; // Localhost testing
$employee_id = $_SESSION['USEROID'] ?? null; // Ensure session is set
$current_date = date("Y-m-d");

?>

<li class="d-flex align-items-center">
    <?php 
    if ($employee_id && (strpos($ip, $allowed_ip_prefix) === 0 || in_array($ip, $allowed_local_ips))) { 
        $check = mysqli_query($conn, "SELECT * FROM employee_attendance WHERE employee_id = '$employee_id' AND date = '$current_date'");

        if (mysqli_num_rows($check) > 0) {
            $row = mysqli_fetch_assoc($check);
            if (is_null($row['check_out_time'])) { ?>
                <button class="btn btn-primary me-3 text-white" id="checkOutBtn">
                    Check - Out
                </button>
            <?php 
            }
        } else { ?>
            <button class="btn btn-primary me-3 text-white" id="checkInBtn">
                Check - In
            </button>
        <?php }
    }?>
</li>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $("#checkInBtn").click(function() {
        if (confirm("Are you sure you want to check in?")) {
            $.post("attendance/checkin.php", function(response) {
                alert(response);
                location.reload();
            });
        }
    });

    $("#checkOutBtn").click(function() {
        if (confirm("Are you sure you want to check out?")) {
            $.post("attendance/checkout.php", function(response) {
                alert(response);
                location.reload();
            });
        }
    });
});
</script>