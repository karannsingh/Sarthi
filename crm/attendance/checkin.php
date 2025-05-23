<?php
require('../include/config.php');

$employee_id = $_SESSION['USEROID'];
$current_datetime = date("Y-m-d H:i:s"); // Correct format for DATETIME column
$current_date = date("Y-m-d"); // Get current date

$time_1015 = strtotime("$current_date 10:15:00");
$time_1100 = strtotime("$current_date 11:00:00");
$now = strtotime("$current_datetime");

$status = '';
$remark = '';

// Check if the employee already checked in today
$check = mysqli_query($conn, "SELECT * FROM employee_attendance WHERE employee_id = '$employee_id' AND date = '$current_date'");

if (mysqli_num_rows($check) > 0) {
    echo "You have already checked in today.";
} else {
    if ($now <= $time_1015) {
        $status = "Present";
    } elseif ($now > $time_1015 && $now <= $time_1100) {
        $remark = "1";
    } else {
        $status = "Half Day";
    }

    // Corrected Query - Inserting full DATETIME
    $query = "INSERT INTO employee_attendance (employee_id, check_in_time, date, status, late_remark) VALUES ('$employee_id', '$current_datetime', '$current_date', '$status', '$remark')";
    
    if (mysqli_query($conn, $query)) {
        if ($status == "Late") {
            echo "Check-In successful. You are Late!";
        } else if ($status == "Half Day") {
            echo "Check-In successful. Marked as Half Day!";
        } else {
            echo "Check-In successful!";
        }
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>