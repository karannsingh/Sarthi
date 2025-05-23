<?php
require('../include/config.php');

if (!isset($_SESSION['USEROID'])) {
    echo "Unauthorized access.";
    exit;
}

$employee_id = mysqli_real_escape_string($conn, $_SESSION['USEROID']);
$current_datetime = date("Y-m-d H:i:s");
$current_date = date("Y-m-d");

// Geolocation
$latitude = isset($_POST['latitude']) ? mysqli_real_escape_string($conn, $_POST['latitude']) : null;
$longitude = isset($_POST['longitude']) ? mysqli_real_escape_string($conn, $_POST['longitude']) : null;

// Validate location
if (!$latitude || !$longitude) {
    echo "Location not captured. Please enable location access.";
    exit;
}

// Fetch employee shift data
$shift_query = mysqli_query($conn, "SELECT * FROM employee_shifts WHERE employee_id = '$employee_id'");
$shift_data = mysqli_fetch_assoc($shift_query);

if (!$shift_data) {
    echo "Shift details not found. Please contact admin.";
    exit;
}

$shift_start = strtotime("$current_date " . $shift_data['shift_start']);
$late_cutoff = strtotime("$current_date " . $shift_data['late_cutoff']);
$current_time = strtotime($current_datetime);

// Check if the employee already checked in today
$check = mysqli_query($conn, "SELECT * FROM employee_attendance WHERE employee_id = '$employee_id' AND date = '$current_date'");

if (mysqli_num_rows($check) > 0) {
    echo "You have already checked in today.";
} else {
    // Determine Status
    if ($current_time <= $shift_start) {
        $status = "Present";
        $late_remark = 0;
    } elseif ($current_time > $shift_start && $current_time <= $late_cutoff) {
        $status = "Late";
        $late_remark = 1;
    } else {
        $status = "Half Day";
        $late_remark = 1;
    }

    // Insert into database
    $query = "INSERT INTO employee_attendance (employee_id, check_in_time, date, status, late_remark, checkin_latitude, checkin_longitude) VALUES ('$employee_id', '$current_datetime', '$current_date', '$status', '$late_remark', '$latitude', '$longitude')";
    
    if (mysqli_query($conn, $query)) {
        echo "Check-In successful!\nStatus: $status\nLate Remark: " . ($late_remark ? 'Yes' : 'No');
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>