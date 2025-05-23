<?php
require_once '../include/config.php';

if (!isset($_POST['manager_oid'])) {
    echo json_encode(['status' => 'error', 'message' => 'Manager OID not provided']);
    exit;
}

$manager_oid = intval($_POST['manager_oid']);

// Get DepartmentOID of the selected manager
$result = mysqli_query($conn, "SELECT DepartmentOID FROM users WHERE UserOID = $manager_oid AND IsDeleted = 0 AND Status = 1");

if ($result && $row = mysqli_fetch_assoc($result)) {
    $dept_oid = intval($row['DepartmentOID']);

    $companies = [];
    $res2 = mysqli_query($conn, "SELECT id, CompanyName FROM master_company WHERE IsDeleted = 0 AND DepartmentOID = $dept_oid ORDER BY CompanyName ASC");

    while ($row2 = mysqli_fetch_assoc($res2)) {
        $companies[] = ['id' => $row2['id'], 'name' => $row2['CompanyName']];
    }

    echo json_encode(['status' => 'success', 'companies' => $companies]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Manager not found or no department assigned']);
}
?>
