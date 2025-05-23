<?php
/**
 * Assign Employee
 * 
 * This script handles assigning an employee to a team leader and department
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
if (empty($_POST['department']) || empty($_POST['teamleader']) || empty($_POST['employee'])) {
    echo json_encode(['status' => 'error', 'message' => 'Department, team leader, and employee are required']);
    exit;
}

// Sanitize inputs
$department_id = mysqli_real_escape_string($conn, trim($_POST['department']));
$teamleader_id = mysqli_real_escape_string($conn, trim($_POST['teamleader']));
$employee_id = mysqli_real_escape_string($conn, trim($_POST['employee']));

// Validate department exists
$validDepartment = mysqli_query($conn, "SELECT DepartmentOID FROM master_department WHERE DepartmentOID = '$department_id'");
if (mysqli_num_rows($validDepartment) === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid department selected']);
    exit;
}

// Validate team leader exists, is assigned, and is active
$validTeamLeader = mysqli_query($conn, "SELECT u.UserOID 
                                       FROM users u
                                       INNER JOIN team_leader_mapping tlm ON u.UserOID = tlm.TeamLeaderUserOID
                                       WHERE u.UserOID = '$teamleader_id' 
                                       AND u.Designation = 3 
                                       AND u.IsDeleted = 0 
                                       AND u.Status = 1");
if (mysqli_num_rows($validTeamLeader) === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid team leader selected or not assigned to this department']);
    exit;
}

// Validate employee exists, is active, and has the correct designation and department
$validEmployee = mysqli_query($conn, "SELECT UserOID 
                                     FROM users 
                                     WHERE UserOID = '$employee_id' 
                                     AND Designation = 4 
                                     AND IsDeleted = 0 
                                     AND Status = 1
                                     AND DepartmentOID = '$department_id'");
if (mysqli_num_rows($validEmployee) === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid employee selected or not in the selected department']);
    exit;
}

// Check if mapping already exists
$checkDuplicate = mysqli_query($conn, "SELECT id FROM employee_mapping 
                                      WHERE EmployeeUserOID = '$employee_id' 
                                      AND TeamLeaderUserOID = '$teamleader_id'");
if (mysqli_num_rows($checkDuplicate) > 0) {
    echo json_encode(['status' => 'error', 'message' => 'This employee is already assigned to this team leader in this department']);
    exit;
}

// Insert the mapping
$insertQuery = "INSERT INTO employee_mapping (TeamLeaderUserOID, EmployeeUserOID, CreatedAt) 
                VALUES ('$teamleader_id', '$employee_id', NOW())";
                
if (mysqli_query($conn, $insertQuery)) {
    echo json_encode(['status' => 'success', 'message' => 'Employee assigned successfully']);
} else {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Failed to assign employee: ' . mysqli_error($conn)
    ]);
}

// Free result sets
mysqli_free_result($validDepartment);
mysqli_free_result($validTeamLeader);
mysqli_free_result($validEmployee);
if (isset($checkDuplicate)) mysqli_free_result($checkDuplicate);
?>