<?php
require('../include/config.php');

if (!isset($_SESSION['USEROID'])) {
    echo "Unauthorized access.";
    exit;
}

$latitude = isset($_POST['latitude']) ? mysqli_real_escape_string($conn, $_POST['latitude']) : null;
$longitude = isset($_POST['longitude']) ? mysqli_real_escape_string($conn, $_POST['longitude']) : null;

if (!$latitude || !$longitude) {
    echo "Checkout location missing. Please refresh and allow location.";
    exit;
}

$employee_id = mysqli_real_escape_string($conn, $_SESSION['USEROID']);
$current_datetime = date("Y-m-d H:i:s"); 
$current_date = date("Y-m-d"); 

// Fetch employee attendance record
$check = mysqli_query($conn, "SELECT * FROM employee_attendance WHERE employee_id = '$employee_id' AND date = '$current_date'");

if (mysqli_num_rows($check) == 0) {
    echo "You need to Check-In first.";
    exit;
}

$row = mysqli_fetch_assoc($check);

if (!is_null($row['check_out_time'])) {
    echo "You Have Already Checked-Out.";
    exit;
}

// Calculate total working hours
$check_in_time = strtotime($row['check_in_time']);
$check_out_time = strtotime($current_datetime);
$total_seconds = $check_out_time - $check_in_time;
$total_hours = gmdate("H:i:s", $total_seconds); 

// Update status based on total hours worked
$hours_worked = $total_seconds / 3600;

// Maintain Late Remark
$late_remark = $row['late_remark']; // Keep existing late status

if ($hours_worked < 4) {
    $status = "Absent";
} elseif ($hours_worked < 6) {
    $status = "Half Day";
} else {
    $status = "Present";
}

// Update the database with check-out time and total hours worked
$query = "UPDATE employee_attendance 
          SET check_out_time = '$current_datetime', 
              total_hours = '$total_hours', 
              status = '$status',
              late_remark = '$late_remark',
              checkout_latitude = '$latitude',
              checkout_longitude = '$longitude'
          WHERE employee_id = '$employee_id' AND date = '$current_date'";
          
if (mysqli_query($conn, $query)) {
    echo "Check-Out successful!\nTotal Hours: $total_hours\nStatus: $status\nLate Remark: " . ($late_remark ? "Yes" : "No");
} else {
    echo "Error: " . mysqli_error($conn);
}
?>