<?php
/**
 * File: get_managers_by_department.php
 * Purpose: Retrieves a list of managers associated with a specific department
 * Used by: User Mapping form - Team Leader tab
 */

// Include database connection
require_once('../include/config.php');

// Initialize response array
$response = [
    'status' => 'error',
    'message' => 'An error occurred while fetching managers.',
    'managers' => []
];

// Check if request is GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

// Validate input
if (!isset($_GET['department_id']) || empty($_GET['department_id'])) {
    $response['message'] = 'Department ID is required.';
    echo json_encode($response);
    exit;
}

// Sanitize input
$department_id = intval($_GET['department_id']);

try {
    // Prepare and execute query
    // Get managers (Designation = 2) who are active (Status = 1) and not deleted (IsDeleted = 0)
    // and who belong to the specified department
    $query = "SELECT u.UserOID, u.UserName 
              FROM users u 
              WHERE u.Designation = 2 
              AND u.DepartmentOID = ? 
              AND u.Status = 1 
              AND u.IsDeleted = 0 
              ORDER BY u.UserName ASC";
    
    // Use prepared statement to prevent SQL injection
    $stmt = mysqli_prepare($conn, $query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $department_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        // Check if any managers found
        if (mysqli_num_rows($result) > 0) {
            $managers = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $managers[] = [
                    'UserOID' => $row['UserOID'],
                    'UserName' => htmlspecialchars($row['UserName'])
                ];
            }
            
            $response = [
                'status' => 'success',
                'message' => 'Managers fetched successfully.',
                'managers' => $managers
            ];
        } else {
            $response = [
                'status' => 'success',
                'message' => 'No managers found for this department.',
                'managers' => []
            ];
        }
        
        mysqli_stmt_close($stmt);
    }
} catch (Exception $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

// Set content type header
header('Content-Type: application/json');

// Return response
echo json_encode($response);
exit;
?>