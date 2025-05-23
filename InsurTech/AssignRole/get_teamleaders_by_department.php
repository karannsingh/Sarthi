<?php
/**
 * File: get_teamleaders_by_department.php
 * Purpose: Retrieves a list of team leaders based on department and optionally filtered by manager
 * Used by: User Mapping form - Team Leader tab and Employee tab
 */

// Include database connection
require_once('../include/config.php');

// Initialize response array
$response = [
    'status' => 'error',
    'message' => 'An error occurred while fetching team leaders.',
    'teamleaders' => []
];

// Check if request is GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

// Validate mandatory input
if (!isset($_GET['department_id']) || empty($_GET['department_id'])) {
    $response['message'] = 'Department ID is required.';
    echo json_encode($response);
    exit;
}

// Sanitize inputs
$department_id = intval($_GET['department_id']);
$manager_id = isset($_GET['manager_id']) ? intval($_GET['manager_id']) : null;
$assigned_only = isset($_GET['assigned_only']) && $_GET['assigned_only'] === 'true';

try {
    // Base query to get team leaders (Designation = 3) for the specified department
    $query = "SELECT DISTINCT u.UserOID, u.UserName 
              FROM users u 
              WHERE u.Designation = 3 
              AND u.DepartmentOID = ? 
              AND u.Status = 1 
              AND u.IsDeleted = 0";
    
    // If manager_id is provided, filter team leaders who are under that manager
    // This is done through a team leader mapping table (you may need to adjust this based on your schema)
    /*if ($manager_id) {
        $query .= " AND u.UserOID IN (
                      SELECT tl_user_id 
                      FROM user_mapping_tl 
                      WHERE manager_user_id = ?
                      AND department_id = ?
                    )";
    }
    
    // If assigned_only is true, we want only team leaders who are already assigned to a manager
    if ($assigned_only) {
        $query .= " AND u.UserOID IN (
                      SELECT tl_user_id 
                      FROM user_mapping_tl 
                      WHERE department_id = ?
                    )";
    }
    */
    $query .= " ORDER BY u.UserName ASC";
    
    // Use prepared statement to prevent SQL injection
    $stmt = mysqli_prepare($conn, $query);
    
    if ($stmt) {
        // Bind parameters based on conditions
        /*if ($manager_id && $assigned_only) {
            mysqli_stmt_bind_param($stmt, "iii", $department_id, $manager_id, $department_id);
        } elseif ($manager_id) {
            mysqli_stmt_bind_param($stmt, "iii", $department_id, $manager_id, $department_id);
        } elseif ($assigned_only) {
            mysqli_stmt_bind_param($stmt, "ii", $department_id, $department_id);
        } else {
            mysqli_stmt_bind_param($stmt, "i", $department_id);
        }*/
        mysqli_stmt_bind_param($stmt, "i", $department_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        // Check if any team leaders found
        if (mysqli_num_rows($result) > 0) {
            $teamleaders = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $teamleaders[] = [
                    'UserOID' => $row['UserOID'],
                    'UserName' => htmlspecialchars($row['UserName'])
                ];
            }
            
            $response = [
                'status' => 'success',
                'message' => 'Team leaders fetched successfully.',
                'teamleaders' => $teamleaders
            ];
        } else {
            $response = [
                'status' => 'success',
                'message' => 'No team leaders found for these criteria.',
                'teamleaders' => []
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