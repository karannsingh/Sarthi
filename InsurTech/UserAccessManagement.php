<?php
require('include/top.php');
require('include/side-navbar.php');
require('include/right-side-navbar.php');

// Check if user is admin
if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] != 1) {
    header("Location: index.php");
    exit();
}

$message = "";
$messageType = "";
$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$userName = "";
$userEmail = "";
$userFullName = "";

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_access'])) {
    $editUserId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    
    if ($editUserId > 0) {
        // Get form data - just the toggle states
        $ipRestriction = isset($_POST['ip_restriction']) ? 1 : 0;
        $irisDetection = isset($_POST['iris_detection']) ? 1 : 0;
        $idleTimeout = (int)$_POST['idle_timeout'];
        
        // Update database - only update the toggle fields
        $updateQuery = "UPDATE users SET 
                        IPRestricted = $ipRestriction, 
                        IrisDetection = $irisDetection,
                        idle_timeout_minutes = $idleTimeout
                        WHERE UserOID = $editUserId";
        
        if (mysqli_query($conn, $updateQuery)) {
            $message = "Access control settings updated successfully!";
            $messageType = "success";
        } else {
            $message = "Error updating access control settings: " . mysqli_error($conn);
            $messageType = "danger";
        }
    } else {
        $message = "Invalid user selected!";
        $messageType = "danger";
    }
}

// If we have a specific user ID, get their details
if ($userId > 0) {
    $userQuery = mysqli_query($conn, "SELECT u.UserOID, u.UserName, u.Email, u.IPRestricted, u.IrisDetection, u.idle_timeout_minutes,
                                      ud.FirstName, ud.LastName 
                                      FROM users u 
                                      LEFT JOIN user_details ud ON u.UserOID = ud.UserOID 
                                      WHERE u.UserOID = $userId and Designation <> 1");
    if ($userQuery && mysqli_num_rows($userQuery) > 0) {
        $userData = mysqli_fetch_assoc($userQuery);
        $userName = $userData['UserName'];
        $userEmail = $userData['Email'];
        $userFullName = trim($userData['FirstName'] . ' ' . $userData['LastName']);
        $ipRestrictionEnabled = $userData['IPRestricted'];
        $irisDetectionEnabled = $userData['IrisDetection'];
        $idleTimeoutMinutes = $userData['idle_timeout_minutes'];
    } else {
        $message = "User not found!";
        $messageType = "danger";
    }
} else {
    // Default values when no user is selected
    $ipRestrictionEnabled = 0;
    $irisDetectionEnabled = 0;
}
?>

<div class="wrapper d-flex flex-column min-vh-100 bg-light bg-opacity-50 dark:bg-transparent">
  <?php require('include/header.php'); ?>
  <div class="body flex-grow-1 px-3">
    <div class="container-lg">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <div class="fs-2 fw-semibold">User Access Management</div>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item">
                <span>Users</span>
              </li>
              <li class="breadcrumb-item">
                <span>Manage Users</span>
              </li>
              <li class="breadcrumb-item active"><span>Access Management</span></li>
            </ol>
          </nav>
        </div>
        <div>
          <a href="ManageUsers.php" class="btn btn-secondary">
            <svg class="icon me-1">
              <use xlink:href="vendor/@coreui/icons/svg/free.svg#cil-arrow-left"></use>
            </svg>
            Back to User List
          </a>
        </div>
      </div>
      
      <?php if (!empty($message)) { ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
          <?php echo $message; ?>
          <button type="button" class="btn-close" data-coreui-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php } ?>
      
      <?php if ($userId == 0) { ?>
      <div class="card mb-4">
        <div class="card-header">
          <strong>Users</strong>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table text-center table-striped table-hover border" id="accessControlTable">
              <thead>
                <tr>
                  <th>#</th>
                  <th>User Full Name</th>
                  <th>Username</th>
                  <th>IP Restriction</th>
                  <th>Iris Detection</th>
                  <th>Idle Timeout (Minutes)</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $accessControlsQuery = mysqli_query($conn, "
                    SELECT U.*, UD.FirstName, UD.LastName 
                    FROM users U 
                    LEFT JOIN user_details UD ON U.UserOID = UD.UserOID
                    WHERE U.IsDeleted = 0 and Designation <> 1
                    ORDER BY UD.FirstName, UD.LastName
                ");
                
                $counter = 1;
                
                while ($access = mysqli_fetch_assoc($accessControlsQuery)) {
                    $fullName = trim($access['FirstName'] . ' ' . $access['LastName']);
                    if (empty($fullName)) {
                        $fullName = 'N/A';
                    }
                    
                    $ipStatus = $access['IPRestricted'] ? 
                        '<span class="badge bg-success">Enabled</span>' : 
                        '<span class="badge bg-secondary">Disabled</span>';
                    $irisStatus = $access['IrisDetection'] ? 
                        '<span class="badge bg-success">Enabled</span>' : 
                        '<span class="badge bg-secondary">Disabled</span>';
                    $idleTimeoutMinutes = $access['idle_timeout_minutes'];
                    
                    echo '<tr>
                            <td>'.$counter.'</td>
                            <td>'.htmlspecialchars($fullName).'</td>
                            <td>'.htmlspecialchars($access['UserName']).'</td>
                            <td>'.$ipStatus.'</td>
                            <td>'.$irisStatus.'</td>
                            <td>'.$idleTimeoutMinutes.'</td>
                            <td>
                                <a href="UserAccessManagement.php?id='.$access['UserOID'].'" class="btn btn-sm btn-primary">
                                    <svg class="icon">
                                        <use xlink:href="vendor/@coreui/icons/svg/free.svg#cil-pencil"></use>
                                    </svg>
                                </a>
                            </td>
                        </tr>';
                    $counter++;
                }
                
                if (mysqli_num_rows($accessControlsQuery) == 0) {
                    echo '<tr><td colspan="6" class="text-center">No users found</td></tr>';
                }
                ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <?php } else { ?>
      <!-- User Edit Form -->
      <div class="card mb-4">
        <div class="card-header">
          <strong>Edit Access Controls for <?php echo htmlspecialchars($userFullName); ?> (<?php echo htmlspecialchars($userName); ?>)</strong>
        </div>
        <div class="card-body">
          <form action="UserAccessManagement.php" method="POST">
            <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
            
            <div class="mb-3">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="ip_restriction" name="ip_restriction" <?php echo $ipRestrictionEnabled ? 'checked' : ''; ?>>
                <label class="form-check-label" for="ip_restriction">Enable IP Restriction</label>
              </div>
              <div id="ip_restriction_info" class="mt-3 <?php echo $ipRestrictionEnabled ? '' : 'd-none'; ?>">
                <div class="alert alert-info">
                  <p class="mb-0">When enabled, this user will only be able to access the system from IP addresses configured in the IP management section.</p>
                </div>
              </div>
            </div>
            
            <div class="mb-3">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="iris_detection" name="iris_detection" <?php echo $irisDetectionEnabled ? 'checked' : ''; ?>>
                <label class="form-check-label" for="iris_detection">Enable Iris Detection Authentication</label>
              </div>
              <div id="iris_detection_info" class="mt-3 alert alert-info <?php echo $irisDetectionEnabled ? '' : 'd-none'; ?>">
                <h6>Important Notice</h6>
                <p>Before enabling iris detection for this user:</p>
                <ol>
                  <li>Ensure the user has compatible iris detection hardware.</li>
                  <li>The user must complete iris registration through their profile settings.</li>
                  <li>Provide training on the iris authentication process.</li>
                </ol>
                <p class="mb-0">If iris detection is enabled without registered iris data, the user may be unable to log in.</p>
              </div>
            </div>

            <div class="mb-3 row">
              <div class="col-sm-3">
                <input type="number" class="form-control" id="idle_timeout" name="idle_timeout" placeholder="Enter timeout in minutes" value="<?php echo $idleTimeoutMinutes; ?>" min="1">
              </div>
              <label for="idle_timeout" class="col-sm-4 col-form-label">Idle Timeout (Minutes)</label>
            </div>

            
            <div class="d-flex justify-content-between">
              <a href="UserAccessManagement.php" class="btn btn-secondary">Cancel</a>
              <button type="submit" name="update_access" class="btn btn-primary">
                <svg class="icon me-1">
                  <use xlink:href="vendor/@coreui/icons/svg/free.svg#cil-save"></use>
                </svg>
                Save Changes
              </button>
            </div>
          </form>
        </div>
      </div>
      <?php } ?>
      
      <div class="card mb-4">
        <div class="card-header">
          <strong>Access Control Information</strong>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-7">
              <div class="card h-100">
                <div class="card-header bg-light">
                  <strong>IP Restriction</strong>
                </div>
                <div class="card-body">
                  <p>IP restriction allows you to restrict from which IP addresses a user can access the system.</p>
                  <p><strong>Important notes:</strong></p>
                  <ul>
                    <li>When enabled, the user will only be able to access the system from authorized IP addresses configured in the IP management section.</li>
                    <li>Ensure that all required IP addresses for the user are properly configured before enabling this feature.</li>
                    <li>This feature is particularly useful for sensitive roles that require strict access control.</li>
                    <li>If a user is unable to login after enabling this feature, verify their IP configuration.</li>
                  </ul>
                </div>
              </div>
            </div>
            <div class="col-md-5">
              <div class="card h-100">
                <div class="card-header bg-light">
                  <strong>Iris Detection</strong>
                </div>
                <div class="card-body">
                  <p>Iris detection adds an additional biometric authentication layer to the user login process.</p>
                  <p><strong>Requirements:</strong></p>
                  <ul>
                    <li>Users must have compatible iris scanning hardware.</li>
                    <li>Users must have already registered their iris scan in the system.</li>
                    <li>Ensure the user has been trained on how to use iris authentication.</li>
                    <li>This feature provides a high level of security for sensitive operations.</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php require('include/footer.php'); ?>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#accessControlTable').DataTable({
        pageLength: 10,
        responsive: true,
        "order": [[1, "asc"]]
    });
    
    // Toggle IP restriction info visibility
    $('#ip_restriction').change(function() {
        if($(this).is(':checked')) {
            $('#ip_restriction_info').removeClass('d-none');
        } else {
            $('#ip_restriction_info').addClass('d-none');
        }
    });
    
    // Toggle iris detection info visibility
    $('#iris_detection').change(function() {
        if($(this).is(':checked')) {
            $('#iris_detection_info').removeClass('d-none');
        } else {
            $('#iris_detection_info').addClass('d-none');
        }
    });
});
</script>