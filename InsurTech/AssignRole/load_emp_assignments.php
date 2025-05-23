<?php
include '../include/config.php';

// Check if user is admin
if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] != 1) {
    echo "Unauthorized access";
    exit;
}

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$query = "SELECT m.id, u1.UserName AS TLName, u2.UserName AS EmpName
          FROM employee_mapping m
          JOIN users u1 ON m.TeamLeaderUserOID = u1.UserOID
          JOIN users u2 ON m.EmployeeUserOID = u2.UserOID
          WHERE u1.UserName LIKE '%$search%' OR u2.UserName LIKE '%$search%'
          ORDER BY m.id DESC";

$res = mysqli_query($conn, $query);

if (!$res) {
    echo "<div class='alert alert-danger'>Error: " . mysqli_error($conn) . "</div>";
    exit;
}

echo "<table class='table table-bordered table-striped table-hover'>
<thead class='table-dark'>
    <tr>
        <th>#</th>
        <th>Team Leader</th>
        <th>Employee</th>
        <th>Action</th>
    </tr>
</thead>
<tbody>";

if (mysqli_num_rows($res) == 0) {
    echo "<tr><td colspan='4' class='text-center'>No records found</td></tr>";
} else {
    $i = 1;
    while ($row = mysqli_fetch_assoc($res)) {
        echo "<tr>
        <td>{$i}</td>
        <td>" . htmlspecialchars($row['TLName']) . "</td>
        <td>" . htmlspecialchars($row['EmpName']) . "</td>
        <td><button class='btn btn-danger btn-sm' onclick='deleteMapping(\"emp\", " . $row['id'] . ")'>Delete</button></td>
        </tr>";
        $i++;
    }
}
echo "</tbody></table>";
?>