<?php
require('include/top.php');
require('include/side-navbar.php');
require('include/right-side-navbar.php');

if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']); // Sanitize input
    $update_query = "UPDATE employee_salary SET IsDeleted = 1 WHERE id = $delete_id";
    mysqli_query($conn, $update_query);
    
    // Redirect to remove the delete_id from the URL
    header("Location: BankDetails.php");
    exit;
}
?>

<div class="wrapper d-flex flex-column min-vh-100 bg-light bg-opacity-50 dark:bg-transparent">
  <?php require('include/header.php'); ?>
  <div class="body flex-grow-1 px-3">
    <div class="container-lg">
      <div class="fs-2 fw-semibold">Bank & Salary Details</div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-4">
          <li class="breadcrumb-item"><span>Home</span></li>
          <li class="breadcrumb-item active"><span>Bank & Salary Details</span></li>
        </ol>
      </nav>

      <div class="row">
        <div class="col-md-12">
          <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
              <strong>Employee Bank & Salary Details</strong>
              <a href="addBankDetails.php" class="btn btn-sm btn-primary">
                <i class="fas fa-plus"></i> Add New
              </a>
            </div>
            <div class="card-body">
              <table class="table table-bordered table-hover align-middle text-center">
                <thead class="thead-light">
                  <tr>
                    <th>Sr. No.</th>
                    <th>User</th>
                    <th>Basic Salary</th>
                    <th>HRA</th>
                    <th>Special Allowance</th>
                    <th>Bank Name</th>
                    <th>Account No</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $query = mysqli_query($conn, "SELECT es.*, u.UserName FROM employee_salary es JOIN users u ON es.employee_id = u.UserOID WHERE es.IsDeleted = 0");
                  $sr_no = 1;
                  while ($row = mysqli_fetch_assoc($query)) {
                  ?>
                    <tr>
                      <td><?= $sr_no++ ?></td>
                      <td><?= htmlspecialchars($row['UserName']) ?></td>
                      <td>₹<?= number_format($row['basic_salary'], 2) ?></td>
                      <td>₹<?= number_format($row['hra'], 2) ?></td>
                      <td>₹<?= number_format($row['special_allowance'], 2) ?></td>
                      <td><?= htmlspecialchars($row['bank_name']) ?></td>
                      <td><?= htmlspecialchars($row['account_number']) ?></td>
                      <td>
                        <a href="addBankDetails.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning" title="Edit">
                          <i class="fas fa-edit"></i>
                        </a>
                        <a href="BankDetails.php?delete_id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Delete this Bank Record?')">
                          <i class="fas fa-trash"></i>
                        </a>
                      </td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div> <!-- card-body -->
          </div> <!-- card -->
        </div> <!-- col -->
      </div> <!-- row -->
    </div> <!-- container -->
  </div> <!-- body -->
</div> <!-- wrapper -->

<?php require('include/footer.php'); ?>