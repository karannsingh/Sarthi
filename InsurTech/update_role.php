<?php
require('include/top.php');
require('include/side-navbar.php');
require('include/right-side-navbar.php');

// Check if user is admin
if (!isset($_SESSION['ROLE']) || $_SESSION['ROLE'] != 1) {
    header("Location: index.php");
    exit();
}

$roles = [1 => 'Admin', 2 => 'Manager', 3 => 'Team Leader', 4 => 'Employee'];
$success = "";
$error = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize inputs
    if (!isset($_POST['user']) || !isset($_POST['new_role'])) {
        $error = "Missing required fields";
    } else {
        $userOID = (int)$_POST['user'];
        $newRole = (int)$_POST['new_role'];
        $adminOID = $_SESSION['USEROID'];
        $forceRemoveMapping = isset($_POST['force_remove']) ? true : false;

        // Validate role
        if (!array_key_exists($newRole, $roles)) {
            $error = "Invalid role selected";
        } else {
            // Fetch current user data
            $userQuery = mysqli_query($conn, "SELECT UserOID, UserName, Designation FROM users WHERE UserOID = $userOID");
            $userData = mysqli_fetch_assoc($userQuery);
            
            if (!$userData) {
                $error = "User not found";
            } else {
                $oldRole = $userData['Designation'];
                $userName = $userData['UserName'];

                // Check if new role is same as current role
                if ($oldRole == $newRole) {
                    $error = "Selected role is same as current role";
                } 
                // Manager demotion check
                elseif ($oldRole == 2 && ($newRole == 3 || $newRole == 4)) {
                    $error = "Error: A Manager cannot be reassigned as Team Leader or Employee";
                } 
                else {
                    $mappingsToRemove = [];
                    
                    // Check existing mappings based on current role
                    if ($oldRole == 2) {
                        // Manager has company/department mappings and team leader mappings
                        $managerMappings = mysqli_query($conn, "SELECT id FROM manager_company_department WHERE ManagerUserOID = $userOID");
                        $tlMappings = mysqli_query($conn, "SELECT id FROM team_leader_mapping WHERE ManagerUserOID = $userOID");
                        
                        if (mysqli_num_rows($managerMappings) > 0 || mysqli_num_rows($tlMappings) > 0) {
                            $mappingsExist = true;
                            $mappingsToRemove[] = "Manager company/department assignments";
                            $mappingsToRemove[] = "Team Leader assignments under this Manager";
                        }
                    }
                    elseif ($oldRole == 3) {
                        // Team Leader has manager mappings and employee mappings
                        $tlManagerMappings = mysqli_query($conn, "SELECT id FROM team_leader_mapping WHERE TeamLeaderUserOID = $userOID");
                        $empMappings = mysqli_query($conn, "SELECT id FROM employee_mapping WHERE TeamLeaderUserOID = $userOID");
                        
                        if (mysqli_num_rows($tlManagerMappings) > 0 || mysqli_num_rows($empMappings) > 0) {
                            $mappingsExist = true;
                            $mappingsToRemove[] = "Manager assignments for this Team Leader";
                            $mappingsToRemove[] = "Employee assignments under this Team Leader";
                        }
                    }
                    elseif ($oldRole == 4) {
                        // Employee has team leader mappings
                        $empMappings = mysqli_query($conn, "SELECT id FROM employee_mapping WHERE EmployeeUserOID = $userOID");
                        
                        if (mysqli_num_rows($empMappings) > 0) {
                            $mappingsExist = true;
                            $mappingsToRemove[] = "Team Leader assignments for this Employee";
                        }
                    }

                    if (!empty($mappingsToRemove) && !$forceRemoveMapping) {
                        $error = "Role change requires removing the following mappings: " . implode(", ", $mappingsToRemove) . 
                                 ". Please confirm to proceed.";
                    } else {
                        // If mappings exist and user confirmed to remove them
                        if (!empty($mappingsToRemove) && $forceRemoveMapping) {
                            // Begin transaction
                            mysqli_begin_transaction($conn);
                            
                            try {
                                // Remove mappings based on current role
                                if ($oldRole == 2) {
                                    mysqli_query($conn, "DELETE FROM manager_company_department WHERE ManagerUserOID = $userOID");
                                    mysqli_query($conn, "DELETE FROM team_leader_mapping WHERE ManagerUserOID = $userOID");
                                } 
                                elseif ($oldRole == 3) {
                                    mysqli_query($conn, "DELETE FROM team_leader_mapping WHERE TeamLeaderUserOID = $userOID");
                                    mysqli_query($conn, "DELETE FROM employee_mapping WHERE TeamLeaderUserOID = $userOID");
                                } 
                                elseif ($oldRole == 4) {
                                    mysqli_query($conn, "DELETE FROM employee_mapping WHERE EmployeeUserOID = $userOID");
                                }
                                
                                // Update role
                                $updateQuery = mysqli_query($conn, "UPDATE users SET Designation = $newRole WHERE UserOID = $userOID");
                                
                                if (!$updateQuery) {
                                    throw new Exception("Failed to update user role");
                                }
                                
                                // Add to role history
                                $historyQuery = mysqli_query($conn, 
                                    "INSERT INTO role_history (UserOID, OldRole, NewRole, ChangedBy) 
                                     VALUES ($userOID, $oldRole, $newRole, $adminOID)");
                                
                                if (!$historyQuery) {
                                    throw new Exception("Failed to record role history");
                                }
                                
                                // Commit transaction
                                mysqli_commit($conn);
                                $success = "Role for '$userName' updated successfully from {$roles[$oldRole]} to {$roles[$newRole]}";
                                
                            } catch (Exception $e) {
                                // Rollback on error
                                mysqli_rollback($conn);
                                $error = "Database error: " . $e->getMessage();
                            }
                        } else {
                            // No mappings or no mapping removal needed
                            $updateQuery = mysqli_query($conn, "UPDATE users SET Designation = $newRole WHERE UserOID = $userOID");
                            
                            if ($updateQuery) {
                                if($oldRole == ""){
                                    $oldRole == 0;
                                }
                                $historyQuery = mysqli_query($conn, 
                                    "INSERT INTO role_history (UserOID, OldRole, NewRole, ChangedBy) 
                                     VALUES ($userOID, $oldRole, $newRole, $adminOID)");
                                
                                if ($historyQuery) {
                                    $success = "Role for '$userName' updated successfully from {$roles[$oldRole]} to {$roles[$newRole]}";
                                } else {
                                    $error = "Role updated, but failed to record history: " . mysqli_error($conn);
                                }
                            } else {
                                $error = "Failed to update role: " . mysqli_error($conn);
                            }
                        }
                    }
                }
            }
        }
    }
}
?>
    <div class="wrapper d-flex flex-column min-vh-100 bg-light bg-opacity-50 dark:bg-transparent">
      <?php
        require('include/header.php');
      ?>
      <div class="body flex-grow-1 px-3">
        <div class="container-lg">
          <div class="fs-2 fw-semibold">Role Management</div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item">
                    <!-- if breadcrumb is single--><span>Users</span>
                </li>
                <li class="breadcrumb-item active"><span>Role Management</span></li>
                </ol>
            </nav>
            <div class="col-md-12">
                <?php if ($success) { ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php } ?>
            </div>
            <div class="col-md-12">
                <?php if ($error) { ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?php echo $error; ?>
                        <?php if (strpos($error, 'mappings') !== false) { ?>
                            <form method="POST" class="mt-3">
                                <input type="hidden" name="user" value="<?php echo $userOID; ?>">
                                <input type="hidden" name="new_role" value="<?php echo $newRole; ?>">
                                <input type="hidden" name="force_remove" value="1">
                                <button type="submit" class="btn btn-warning">Yes, Remove Mappings and Update Role</button>
                            </form>
                        <?php } ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php } ?>
            </div>
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Update User Role</h4>
                </div>
                <div class="card-body">
                    <form method="POST" onsubmit="return confirm('Are you sure you want to update this user\'s role?');">
                        <div class="mb-3">
                            <label class="form-label">Select User</label>
                            <select name="user" class="form-select" required onchange="showCurrentRole(this.value)">
                                <option value="">-- Select User --</option>
                                <?php
                                $users = mysqli_query($conn, "SELECT UserOID, UserName, Designation FROM users WHERE IsDeleted=0 AND Status=0 ORDER BY UserName ASC");
                                while ($row = mysqli_fetch_assoc($users)) {
                                    echo "<option value='" . htmlspecialchars($row['UserOID']) . "' data-role='" . 
                                        htmlspecialchars($roles[$row['Designation']]) . "'>" . 
                                        htmlspecialchars($row['UserName']) . " (" . htmlspecialchars($roles[$row['Designation']]) . ")</option>";
                                }
                                ?>
                            </select>
                            <div id="currentRoleText" class="text-muted mt-2"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Select New Role</label>
                            <select name="new_role" class="form-select" required>
                                <option value="">-- Select Role --</option>
                                <option value="1">Admin</option>
                                <option value="2">Manager</option>
                                <option value="3">Team Leader</option>
                                <option value="4">Employee</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-success">Update Role</button>
                    </form>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header bg-secondary text-white">
                    <h4 class="mb-0">Recent Role Changes</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr class="table-dark">
                                    <th>User</th>
                                    <th>Old Role</th>
                                    <th>New Role</th>
                                    <th>Changed On</th>
                                    <th>Changed By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $roleChanges = mysqli_query($conn, "
                                    SELECT rh.*, u1.UserName AS UserName, u2.UserName AS ChangedByName
                                    FROM role_history rh
                                    LEFT JOIN users u1 ON rh.UserOID = u1.UserOID
                                    LEFT JOIN users u2 ON rh.ChangedBy = u2.UserOID
                                    ORDER BY rh.ChangedOn DESC LIMIT 10
                                ");
                                
                                if (mysqli_num_rows($roleChanges) == 0) {
                                    echo "<tr><td colspan='5' class='text-center'>No recent role changes found</td></tr>";
                                } else {
                                    while ($r = mysqli_fetch_assoc($roleChanges)) {
                                        $oldRoleName = isset($roles[$r['OldRole']]) ? $roles[$r['OldRole']] : 'Unknown';
                                        $newRoleName = isset($roles[$r['NewRole']]) ? $roles[$r['NewRole']] : 'Unknown';
                                        
                                        echo "<tr>
                                                <td>" . htmlspecialchars($r['UserName']) . "</td>
                                                <td>" . htmlspecialchars($oldRoleName) . "</td>
                                                <td>" . htmlspecialchars($newRoleName) . "</td>
                                                <td>" . htmlspecialchars($r['ChangedOn']) . "</td>
                                                <td>" . htmlspecialchars($r['ChangedByName']) . "</td>
                                              </tr>";
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>      
<?php
require('include/footer.php');
?>
<script>
function showCurrentRole(userId) {
    var selectedOption = $('select[name="user"] option:selected');
    var currentRole = selectedOption.data('role') || "";
    $('#currentRoleText').text(currentRole ? "Current Role: " + currentRole : "");
}
</script>