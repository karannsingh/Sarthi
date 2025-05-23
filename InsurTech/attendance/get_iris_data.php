<?php
// Database connection
require("../include/config.php");

// Get employee ID from POST request
$employee_id = $_POST['employee_id'] ?? null;

// Fetch iris data from database
$query = "SELECT iris_data FROM users WHERE UserOID = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $employee_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    echo json_encode([
        "status" => "success",
        "iris_data" => $row['iris_data']
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "No iris data found for employee ID: " . $employee_id
    ]);
}

mysqli_close($conn);
?>