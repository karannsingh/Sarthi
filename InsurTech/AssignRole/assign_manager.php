<?php
/**
 * Assign Manager
 * 
 * This script handles assigning a manager to a company
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
if (empty($_POST['manager']) || empty($_POST['company'])) {
    echo json_encode(['status' => 'error', 'message' => 'Manager and company are required']);
    exit;
}

// Sanitize inputs
$manager_id = mysqli_real_escape_string($conn, trim($_POST['manager']));
$company_id = mysqli_real_escape_string($conn, trim($_POST['company']));

// Validate manager exists and is a manager (Designation = 2)
$validManager = mysqli_query($conn, "SELECT UserOID FROM users WHERE UserOID = '$manager_id' AND Designation = 2 AND IsDeleted = 0 AND Status = 1");
if (mysqli_num_rows($validManager) === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid manager selected']);
    exit;
}

// Validate company exists and is not deleted
$validCompany = mysqli_query($conn, "SELECT id FROM master_company WHERE id = '$company_id' AND IsDeleted = 0");
if (mysqli_num_rows($validCompany) === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid company selected']);
    exit;
}

// Check if mapping already exists
$checkDuplicate = mysqli_query($conn, "SELECT id FROM manager_company_department 
                                      WHERE ManagerUserOID = '$manager_id' AND CompanyOID = '$company_id'");
if (mysqli_num_rows($checkDuplicate) > 0) {
    echo json_encode(['status' => 'error', 'message' => 'This manager is already assigned to this company']);
    exit;
}

// Insert the mapping
$insertQuery = "INSERT INTO manager_company_department (ManagerUserOID, CompanyOID, CreatedAt) 
                VALUES ('$manager_id', '$company_id', NOW())";
                
if (mysqli_query($conn, $insertQuery)) {
    echo json_encode(['status' => 'success', 'message' => 'Manager assigned successfully']);
} else {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Failed to assign manager: ' . mysqli_error($conn)
    ]);
}

// Free result sets
mysqli_free_result($validManager);
mysqli_free_result($validCompany);
if (isset($checkDuplicate)) mysqli_free_result($checkDuplicate);
?>