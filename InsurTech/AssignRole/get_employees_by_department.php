<?php
/**
 * File: get_employees_by_department.php
 * Purpose: Retrieves a list of employees based on department and team leader
 * Used by: User Mapping form - Employee tab
 */

// Include database connection
require_once('../include/config.php');

// Initialize response array
$response = [
    'status' => 'error',
    'message' => 'An error occurred while fetching employees.',
    'employees' => []
];

// Check if request is GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

// Validate mandatory inputs
if (!isset($_GET['department_id']) || empty($_GET['department_id'])) {
    $response['message'] = 'Department ID is required.';
    echo json_encode($response);
    exit;
}

if (!isset($_GET['teamleader_id']) || empty($_GET['teamleader_id'])) {
    $response['message'] = 'Team Leader ID is required.';
    echo json_encode($response);
    exit;
}

// Sanitize inputs
$department_id = intval($_GET['department_id']);
$teamleader_id = intval($_GET['teamleader_id']);

try {
    // Query to get employees (Designation = 4) from the department who aren't already assigned
    // This query gets employees who are:
    // 1. Employees (Designation = 4)
    // 2. In the specified department
    // 3. Active (Status = 1)
    // 4. Not deleted (IsDeleted = 0)
    // 5. Not already assigned to any team leader (not in user_mapping_emp table)
    $query = "SELECT u.UserOID, u.UserName 
              FROM users u 
              WHERE u.Designation = 4 
              AND u.DepartmentOID = ? 
              AND u.Status = 1 
              AND u.IsDeleted = 0 
              AND u.UserOID NOT IN (
                SELECT EmployeeUserOID
                FROM employee_mapping
              ) 
              ORDER BY u.UserName ASC";
    
    // Use prepared statement to prevent SQL injection
    $stmt = mysqli_prepare($conn, $query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $department_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        // Check if any employees found
        if (mysqli_num_rows($result) > 0) {
            $employees = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $employees[] = [
                    'UserOID' => $row['UserOID'],
                    'UserName' => htmlspecialchars($row['UserName'])
                ];
            }
            
            $response = [
                'status' => 'success',
                'message' => 'Employees fetched successfully.',
                'employees' => $employees
            ];
        } else {
            $response = [
                'status' => 'success',
                'message' => 'No available employees found for this department.',
                'employees' => []
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