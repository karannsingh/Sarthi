<?php
/**
 * Load Manager Assignments
 * 
 * This script loads and displays all manager company assignments with search functionality
 */

// Include database configuration
require_once '../include/config.php';

// Check if user is admin
if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] != 1) {
    http_response_code(403);
    exit('Unauthorized access');
}

// Sanitize search input if provided
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';

// Prepare the query with search condition if search parameter exists
$searchCondition = !empty($search) ? 
    "AND (u.UserName LIKE '%$search%' OR c.CompanyName LIKE '%$search%')" : "";

// Query to fetch manager assignments with company info
$query = "SELECT mcd.id, u.UserOID, u.UserName, c.id AS CompanyID, c.CompanyName FROM manager_company_department mcd INNER JOIN users u ON mcd.ManagerUserOID = u.UserOID INNER JOIN master_company c ON mcd.CompanyOID = c.id WHERE u.IsDeleted = 0 AND c.IsDeleted = 0 $searchCondition
          ORDER BY u.UserName ASC";

$result = mysqli_query($conn, $query);

// Check if query was successful
if (!$result) {
    echo '<div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle"></i> Error loading data: ' . mysqli_error($conn) . '
          </div>';
    exit;
}

// Check if any records found
if (mysqli_num_rows($result) === 0) {
    echo '<div class="alert alert-info">
            <i class="bi bi-info-circle"></i> ' . 
            (empty($search) ? 'No manager assignments found.' : 'No results found for your search.') . '
          </div>';
} else {
    // Display data in a table
    ?>
    <table class="table table-striped table-hover">
        <thead class="table-light">
            <tr>
                <th scope="col">#</th>
                <th scope="col">Manager Name</th>
                <th scope="col">Company</th>
                <th scope="col" class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $count = 1;
            while ($row = mysqli_fetch_assoc($result)) {
                ?>
                <tr>
                    <td><?= $count++ ?></td>
                    <td><?= htmlspecialchars($row['UserName']) ?></td>
                    <td><?= htmlspecialchars($row['CompanyName']) ?></td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-danger" 
                                onclick="deleteMapping('manager', <?= $row['id'] ?>)">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>
    <?php
}

// Free result set
mysqli_free_result($result);
?>