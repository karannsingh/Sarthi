<?php
/**
 * Assign Team Leader
 * 
 * This script handles assigning a team leader to a manager and department
 */

// Include database configuration
require_once '../include/config.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is admin
if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] != 1) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

// Validate required fields
if (empty($_POST['department']) || empty($_POST['manager']) || empty($_POST['teamleader'])) {
    echo json_encode(['status' => 'error', 'message' => 'Department, manager, and team leader are required']);
    exit;
}

// Sanitize inputs
$department_id = mysqli_real_escape_string($conn, trim($_POST['department']));
$manager_id = mysqli_real_escape_string($conn, trim($_POST['manager']));
$teamleader_id = mysqli_real_escape_string($conn, trim($_POST['teamleader']));

// Validate department exists
$validDepartment = mysqli_query($conn, "SELECT DepartmentOID FROM master_department WHERE DepartmentOID = '$department_id'");
if (mysqli_num_rows($validDepartment) === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid department selected']);
    exit;
}

// Validate manager exists, is active, and is assigned to this department
$validManager = mysqli_query($conn, "SELECT u.UserOID 
                                   FROM users u
                                   INNER JOIN manager_company_department mcd ON u.UserOID = mcd.ManagerUserOID
                                   WHERE u.UserOID = '$manager_id' 
                                   AND u.Designation = 2 
                                   AND u.IsDeleted = 0 
                                   AND u.Status = 1");
if (mysqli_num_rows($validManager) === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid manager selected']);
    exit;
}

// Validate team leader exists, is active, and has the correct designation
$validTeamLeader = mysqli_query($conn, "SELECT UserOID 
                                       FROM users 
                                       WHERE UserOID = '$teamleader_id' 
                                       AND Designation = 3 
                                       AND IsDeleted = 0 
                                       AND Status = 1
                                       AND DepartmentOID = '$department_id'");
if (mysqli_num_rows($validTeamLeader) === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid team leader selected or not in the selected department']);
    exit;
}

// Check if mapping already exists
$checkDuplicate = mysqli_query($conn, "SELECT id FROM team_leader_mapping 
                                      WHERE TeamLeaderUserOID = '$teamleader_id' 
                                      AND ManagerUserOID = '$manager_id'");
if (mysqli_num_rows($checkDuplicate) > 0) {
    echo json_encode(['status' => 'error', 'message' => 'This team leader is already assigned to this manager in this department']);
    exit;
}

// Insert the mapping
$insertQuery = "INSERT INTO team_leader_mapping (ManagerUserOID, TeamLeaderUserOID, CreatedAt) 
                VALUES ('$manager_id', '$teamleader_id', NOW())";
                
if (mysqli_query($conn, $insertQuery)) {
    echo json_encode(['status' => 'success', 'message' => 'Team leader assigned successfully']);
} else {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Failed to assign team leader: ' . mysqli_error($conn)
    ]);
}

// Free result sets
mysqli_free_result($validDepartment);
mysqli_free_result($validManager);
mysqli_free_result($validTeamLeader);
if (isset($checkDuplicate)) mysqli_free_result($checkDuplicate);
?>