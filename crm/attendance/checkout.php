<?php
require('../include/config.php');

$employee_id = $_SESSION['USEROID']; // Get logged-in employee ID
$current_datetime = date("Y-m-d H:i:s"); // Current date and time
$current_date = date("Y-m-d"); // Current date only

// Fetch employee attendance record
$check = mysqli_query($conn, "SELECT * FROM employee_attendance WHERE employee_id = '$employee_id' AND date = '$current_date'");

if (mysqli_num_rows($check) == 0) {
    echo "You need to Check-In first.";
} else {
    $row = mysqli_fetch_assoc($check);

    if (!is_null($row['check_out_time'])) {
        echo "You Have Already Checked-Out.";
    } else {
        // Calculate total working hours
        $check_in_time = strtotime($row['check_in_time']);
        $check_out_time = strtotime($current_datetime);
        $total_seconds = $check_out_time - $check_in_time;
        $total_hours = gmdate("H:i:s", $total_seconds); // Format total time in HH:MM:SS

        // Update status based on total hours worked
        $hours_worked = $total_seconds / 3600; // Convert seconds to hours
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
                      status = '$status' 
                  WHERE employee_id = '$employee_id' AND date = '$current_date'";

        if (mysqli_query($conn, $query)) {
            echo "Check-Out successful! Total Hours: $total_hours, Status: $status";
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
}
?>