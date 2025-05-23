<?php
// Include your database connection
require_once('../include/config.php');

// Check if the employee ID is provided
if (!isset($_POST['employee_id']) || empty($_POST['employee_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Employee ID not provided'
    ]);
    exit;
}

$employee_id = mysqli_real_escape_string($conn, $_POST['employee_id']);

// Get the iris data from the database
$query = "SELECT iris_data FROM users WHERE UserOID = '$employee_id' AND IsDeleted = 0";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) === 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Employee not found or iris data not available'
    ]);
    exit;
}

$row = mysqli_fetch_assoc($result);
$iris_data = $row['iris_data'];

if (empty($iris_data)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Iris data not available for this employee'
    ]);
    exit;
}

// Return the iris data
echo json_encode([
    'status' => 'success',
    'iris_data' => $iris_data
]);
?>