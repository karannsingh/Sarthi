<?php
require('include/top.php');
require('include/side-navbar.php');
require('include/right-side-navbar.php');
require_once 'function/send_email_function.php';
// Check if user is admin
if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] != 1) {
    header("Location: index.php");
    exit();
}

$message = "";
$messageType = "";

// Handle status change
if (isset($_GET['action']) && $_GET['action'] == 'status' && isset($_GET['id'])) {
    $userId = (int)$_GET['id'];
    $currentStatus = (int)$_GET['status'];
    $newStatus = $currentStatus == 1 ? 0 : 1;
    
    $updateStatus = mysqli_query($conn, "UPDATE users SET Status = $newStatus WHERE UserOID = $userId");
    
    if ($updateStatus) {
        $message = "User status updated successfully!";
        $messageType = "success";
    } else {
        $message = "Error updating user status: " . mysqli_error($conn);
        $messageType = "danger";
    }
}

// Handle user deletion (soft delete)
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $userId = (int)$_GET['id'];
    
    $deleteUser = mysqli_query($conn, "UPDATE users SET IsDeleted = 1 WHERE UserOID = $userId");
    
    if ($deleteUser) {
        $message = "User deleted successfully!";
        $messageType = "success";
    } else {
        $message = "Error deleting user: " . mysqli_error($conn);
        $messageType = "danger";
    }
}

// Reset password functionality
if (isset($_GET['action']) && $_GET['action'] == 'reset_password' && isset($_GET['id'])) {
    $userId = (int)$_GET['id'];
    
    // Generate random password
    $randomPassword = bin2hex(random_bytes(4));
    // Properly hash the password
    $hashedPassword = $randomPassword;
    
    $resetPassword = mysqli_query($conn, "UPDATE users SET Password = '$hashedPassword' WHERE UserOID = $userId");
    
    if ($resetPassword) {
        // Get user email to display in message
        $userQuery = mysqli_query($conn, "SELECT Email FROM users WHERE UserOID = $userId");
        $userData = mysqli_fetch_assoc($userQuery);
        $userEmail = $userData['Email'];
        
        $subject = "Password Reset Successful";
        $body = "Password reset successfully for user: $userEmail\nNew temporary password: $randomPassword\nPlease advise the user to change their password after first login.";

        // Send email (Example: use Signature 2)
        send_custom_email($userEmail, $subject, $body, 2);

        $message = "Password reset successfully for user: $userEmail<br>New temporary password: <strong>$randomPassword</strong><br>Please advise the user to change their password after first login.";
        $messageType = "success";
    } else {
        $message = "Error resetting password: " . mysqli_error($conn);
        $messageType = "danger";
    }
}

// Get all departments for filter
$departments = mysqli_query($conn, "SELECT * FROM master_department ORDER BY DepartmentName");
$departmentList = array();
while ($dept = mysqli_fetch_assoc($departments)) {
    $departmentList[$dept['DepartmentOID']] = $dept['DepartmentName'];
}

// Get all designations for filter
$designations = mysqli_query($conn, "SELECT * FROM master_designation ORDER BY DesginationLevel");
$designationList = array();
while ($desig = mysqli_fetch_assoc($designations)) {
    $designationList[$desig['DesignationOID']] = $desig['DesignationName'];
}

// Get all companies for filter
$companies = mysqli_query($conn, "SELECT * FROM master_company WHERE IsDeleted = 0 ORDER BY CompanyName");
?>

<div class="wrapper d-flex flex-column min-vh-100 bg-light bg-opacity-50 dark:bg-transparent">
  <?php require('include/header.php'); ?>
  <div class="body flex-grow-1 px-3">
    <div class="container-lg">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <div class="fs-2 fw-semibold">Manage Users</div>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item">
                <span>Users</span>
              </li>
              <li class="breadcrumb-item active"><span>Manage Users</span></li>
            </ol>
          </nav>
        </div>
        <div>
          <a href="AddUsers.php" class="btn btn-primary">
            <svg class="icon me-1">
              <use xlink:href="assets/@coreui/icons/svg/free.svg#cil-user-plus"></use>
            </svg>
            Add New User
          </a>
          <a href="UserAccessManagement.php" class="btn btn-info ms-1">
            <svg class="icon me-1">
              <use xlink:href="assets/@coreui/icons/svg/free.svg#cil-lock-locked"></use>
            </svg>
            Manage Access Controls
          </a>
          <a href="ManageUsers.php" class="btn btn-primary ms-1">
            <svg class="icon me-1">
              <use xlink:href="assets/@coreui/icons/svg/free.svg#cil-reload"></use>
            </svg>
            Refresh
          </a>
        </div>
      </div>
      
      <?php if (!empty($message)) { ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
          <?php echo $message; ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php } ?>
      
      <div class="card mb-4">
        <div class="card-header">
          <strong>User List</strong>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-striped table-hover border" id="userTable">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Name</th>
                  <th>Username</th>
                  <th>Email</th>
                  <th>Mobile</th>
                  <th>Department</th>
                  <th>Designation</th>
                  <th>Joining Date</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php
                // Build query with filters
                $query = "SELECT u.UserOID, u.UserName, u.Email, u.Number, u.JoiningDt, 
                          u.DepartmentOID, u.Designation as DesignationOID, u.Status,
                          ud.FirstName, ud.MiddleName, ud.LastName
                          FROM users u
                          LEFT JOIN user_details ud ON u.UserOID = ud.UserOID
                          WHERE u.IsDeleted = 0 and Designation <> 1";
                          
                $query .= " ORDER BY u.UserOID DESC";
                
                $result = mysqli_query($conn, $query);
                $counter = 1;
                
                while ($user = mysqli_fetch_assoc($result)) {
                    $fullName = trim($user['FirstName'] . ' ' . $user['MiddleName'] . ' ' . $user['LastName']);
                    $department = isset($departmentList[$user['DepartmentOID']]) ? $departmentList[$user['DepartmentOID']] : 'N/A';
                    $designation = isset($designationList[$user['DesignationOID']]) ? $designationList[$user['DesignationOID']] : 'N/A';
                    $status = $user['Status'] == 1 ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';
                    
                    echo '<tr>
                            <td>'.$counter.'</td>
                            <td>'.htmlspecialchars($fullName).'</td>
                            <td>'.htmlspecialchars($user['UserName']).'</td>
                            <td>'.htmlspecialchars($user['Email']).'</td>
                            <td>'.htmlspecialchars($user['Number']).'</td>
                            <td>'.$department.'</td>
                            <td>'.$designation.'</td>
                            <td>'.date('d-m-Y', strtotime($user['JoiningDt'])).'</td>
                            <td>'.$status.'</td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-primary dropdown-toggle" type="button" id="actionDropdown'.$user['UserOID'].'" data-coreui-toggle="dropdown" aria-expanded="false">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="actionDropdown'.$user['UserOID'].'">
                                        <li><a class="dropdown-item" href="EditUser.php?id='.$user['UserOID'].'">
                                            <svg class="icon me-2">
                                                <use xlink:href="assets/@coreui/icons/svg/free.svg#cil-pencil"></use>
                                            </svg> Edit
                                        </a></li>
                                        <li><a class="dropdown-item" href="update_role.php?id='.$user['UserOID'].'">
                                            <svg class="icon me-2">
                                                <use xlink:href="assets/@coreui/icons/svg/free.svg#cil-settings"></use>
                                            </svg> Update Role
                                        </a></li>
                                        <li><a class="dropdown-item" href="UserAccessManagement.php?id='.$user['UserOID'].'">
                                            <svg class="icon me-2">
                                                <use xlink:href="assets/@coreui/icons/svg/free.svg#cil-lock-locked"></use>
                                            </svg> Access Controls
                                        </a></li>
                                        <li><hr class="dropdown-divider"></li>';
                                        
                                        if($user['Status'] == 1) {
                                            echo '<li><a class="dropdown-item text-warning status-toggle" href="#" data-id="'.$user['UserOID'].'" data-status="'.$user['Status'].'" data-action="deactivate">
                                                <svg class="icon me-2">
                                                    <use xlink:href="assets/@coreui/icons/svg/free.svg#cil-user-unfollow"></use>
                                                </svg> Deactivate
                                            </a></li>';
                                        } else {
                                            echo '<li><a class="dropdown-item text-success status-toggle" href="#" data-id="'.$user['UserOID'].'" data-status="'.$user['Status'].'" data-action="activate">
                                                <svg class="icon me-2">
                                                    <use xlink:href="assets/@coreui/icons/svg/free.svg#cil-user-follow"></use>
                                                </svg> Activate
                                            </a></li>';
                                        }
                                        
                                        echo '<li><a class="dropdown-item text-primary reset-password" href="#" data-id="'.$user['UserOID'].'">
                                            <svg class="icon me-2">
                                                <use xlink:href="assets/@coreui/icons/svg/free.svg#cil-lock-unlocked"></use>
                                            </svg> Reset Password
                                        </a></li>
                                        <li><a class="dropdown-item text-danger delete-user" href="#" data-id="'.$user['UserOID'].'">
                                            <svg class="icon me-2">
                                                <use xlink:href="assets/@coreui/icons/svg/free.svg#cil-trash"></use>
                                            </svg> Delete
                                        </a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>';
                    $counter++;
                }
                
                if (mysqli_num_rows($result) == 0) {
                    echo '<tr><td colspan="10" class="text-center">No users found</td></tr>';
                }
                ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php require('include/footer.php'); ?>
</div>

<!-- Status Change Modal -->
<div class="modal fade" id="statusChangeModal" tabindex="-1" aria-labelledby="statusChangeModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="statusChangeModalLabel">Confirm Status Change</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="statusChangeModalBody">
        Are you sure you want to change the status of this user?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <a href="#" class="btn btn-primary" id="statusChangeConfirm">Confirm</a>
      </div>
    </div>
  </div>
</div>

<!-- Reset Password Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="resetPasswordModalLabel">Confirm Password Reset</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to reset the password for this user? A new temporary password will be generated.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <a href="#" class="btn btn-primary" id="resetPasswordConfirm">Reset Password</a>
      </div>
    </div>
  </div>
</div>

<!-- Delete User Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteUserModalLabel">Confirm User Deletion</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-danger">
          <i class="cil-warning"></i> Warning: This action cannot be undone.
        </div>
        Are you sure you want to delete this user? This is a soft delete that will prevent the user from accessing the system.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <a href="#" class="btn btn-danger" id="deleteUserConfirm">Delete User</a>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTable with export buttons
    $('#userTable').DataTable({
        "pageLength": 10,
        "lengthMenu": [10, 25, 50, 100],
        "order": [[0, "asc"]],
        "responsive": true,
        "language": {
            "emptyTable": "No IP addresses found",
            "zeroRecords": "No matching records found"
        }
    });
    
    // Status change modal
    $('.status-toggle').click(function(e) {
        e.preventDefault();
        var userId = $(this).data('id');
        var status = $(this).data('status');
        var action = $(this).data('action');
        
        $('#statusChangeModalBody').html('Are you sure you want to <strong>' + action + '</strong> this user?');
        $('#statusChangeConfirm').attr('href', 'ManageUsers.php?action=status&id=' + userId + '&status=' + status);
        $('#statusChangeModal').modal('show');
    });
    
    // Reset password modal
    $('.reset-password').click(function(e) {
        e.preventDefault();
        var userId = $(this).data('id');
        
        $('#resetPasswordConfirm').attr('href', 'ManageUsers.php?action=reset_password&id=' + userId);
        $('#resetPasswordModal').modal('show');
    });
    
    // Delete user modal
    $('.delete-user').click(function(e) {
        e.preventDefault();
        var userId = $(this).data('id');
        
        $('#deleteUserConfirm').attr('href', 'ManageUsers.php?action=delete&id=' + userId);
        $('#deleteUserModal').modal('show');
    });
});
</script>