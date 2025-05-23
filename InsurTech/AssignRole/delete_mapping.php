<!-- AssignRole/delete_mapping.php -->
<?php
include '../include/config.php';

// Check if user is admin
if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] != 1) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

// Validate input
if (!isset($_POST['type']) || !isset($_POST['id']) || !is_numeric($_POST['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request parameters']);
    exit;
}

$type = $_POST['type'];
$id = mysqli_real_escape_string($conn, $_POST['id']);

// Define allowed mapping tables
$tbl = [
    "manager" => "manager_company_department",
    "tl" => "team_leader_mapping",
    "emp" => "employee_mapping"
];

if (!array_key_exists($type, $tbl)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid mapping type']);
    exit;
}

$table = $tbl[$type];

// Delete the mapping
$del = mysqli_query($conn, "DELETE FROM $table WHERE id = '$id'");

if ($del) {
    echo json_encode(['status' => 'success', 'message' => 'Mapping deleted successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to delete mapping: ' . mysqli_error($conn)]);
}
?>