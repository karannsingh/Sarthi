<?php
require('include/top.php');
require('include/side-navbar.php');
require('include/right-side-navbar.php');

// Example: Getting currently logged-in user's ID
$user_id = $_SESSION['USEROID'];

// Get user info
$user_query = mysqli_query($conn, "SELECT u.*, ud.* FROM users u 
                                    LEFT JOIN user_details ud ON u.UserOID = ud.UserOID 
                                    WHERE u.UserOID = $user_id");
$user = mysqli_fetch_assoc($user_query);

// Get department and designation
$dept_query = mysqli_query($conn, "SELECT DepartmentName FROM master_department WHERE DepartmentOID = {$user['DepartmentOID']}");
$dept = mysqli_fetch_assoc($dept_query)['DepartmentName'] ?? 'N/A';

$desig_query = mysqli_query($conn, "SELECT DesignationName FROM master_designation WHERE DesignationOID = {$user['Designation']}");
$desig = mysqli_fetch_assoc($desig_query)['DesignationName'] ?? 'N/A';

// Get salary info
$salary_query = mysqli_query($conn, "SELECT * FROM employee_salary WHERE employee_id = $user_id");
$salary = mysqli_fetch_assoc($salary_query);

// Get shift info
$shift_query = mysqli_query($conn, "SELECT * FROM employee_shifts WHERE employee_id = $user_id");
$shift = mysqli_fetch_assoc($shift_query);

// Calculate total earnings and deductions
$total_earnings = 0;
$total_deductions = 0;

if ($salary) {
    $total_earnings = $salary['basic_salary'] + $salary['hra'] + $salary['special_allowance'];
    $total_deductions = $salary['pf_employee_contribution'] + $salary['professional_tax'];
    $net_salary = $total_earnings - $total_deductions;
}

// Format Aadhar number with spaces
function formatAadhar($aadhar) {
    if (strlen($aadhar) == 12) {
        return substr($aadhar, 0, 4) . ' ' . substr($aadhar, 4, 4) . ' ' . substr($aadhar, 8, 4);
    }
    return $aadhar;
}

// Get full name
$fullName = $user['FirstName'] . ' ' . $user['MiddleName'] . ' ' . $user['LastName'];
$fullName = trim(preg_replace('/ +/', ' ', $fullName));

$loggedInUserOID = $user_id;
$role = $_SESSION['ROLE'];

$tree = [];

// Dynamic SQL based on role
if ($role == '2') {
    $sql = "SELECT DISTINCT 
                mgr.UserName AS ManagerName, 
                tl.UserName AS TeamLeaderName, 
                emp.UserName AS EmployeeName 
            FROM manager_company_department mcd 
            JOIN users mgr ON mcd.ManagerUserOID = mgr.UserOID 
            LEFT JOIN team_leader_mapping tlm ON mcd.ManagerUserOID = tlm.ManagerUserOID 
            LEFT JOIN users tl ON tlm.TeamLeaderUserOID = tl.UserOID 
            LEFT JOIN employee_mapping em ON em.TeamLeaderUserOID = tl.UserOID 
            LEFT JOIN users emp ON em.EmployeeUserOID = emp.UserOID 
            WHERE mcd.ManagerUserOID = $loggedInUserOID 
            ORDER BY mgr.UserName, tl.UserName, emp.UserName";
} elseif ($role == '3') {
    $sql = "SELECT DISTINCT 
                mgr.UserName AS ManagerName, 
                tl.UserName AS TeamLeaderName, 
                emp.UserName AS EmployeeName 
            FROM team_leader_mapping tlm 
            JOIN users tl ON tlm.TeamLeaderUserOID = tl.UserOID 
            JOIN manager_company_department mcd ON tlm.ManagerUserOID = mcd.ManagerUserOID 
            JOIN users mgr ON mcd.ManagerUserOID = mgr.UserOID 
            LEFT JOIN employee_mapping em ON em.TeamLeaderUserOID = tl.UserOID 
            LEFT JOIN users emp ON em.EmployeeUserOID = emp.UserOID 
            WHERE tl.UserOID = $loggedInUserOID 
            ORDER BY mgr.UserName, tl.UserName, emp.UserName";
} elseif ($role == '4') {
    $sql = "SELECT DISTINCT 
                mgr.UserName AS ManagerName, 
                tl.UserName AS TeamLeaderName, 
                emp.UserName AS EmployeeName 
            FROM employee_mapping em 
            JOIN users emp ON em.EmployeeUserOID = emp.UserOID 
            JOIN users tl ON em.TeamLeaderUserOID = tl.UserOID 
            JOIN team_leader_mapping tlm ON em.TeamLeaderUserOID = tlm.TeamLeaderUserOID 
            JOIN users mgr ON tlm.ManagerUserOID = mgr.UserOID 
            WHERE emp.UserOID = $loggedInUserOID 
            ORDER BY mgr.UserName, tl.UserName, emp.UserName";
}

if($role != '1'){
  $result = $conn->query($sql);

  while ($row = $result->fetch_assoc()) {
      $manager = $row['ManagerName'];
      $teamLeader = $row['TeamLeaderName'];
      $employee = $row['EmployeeName'];

      if (!isset($tree[$manager])) {
          $tree[$manager] = [];
      }
      if ($teamLeader && !isset($tree[$manager][$teamLeader])) {
          $tree[$manager][$teamLeader] = [];
      }
      if ($employee) {
          $tree[$manager][$teamLeader][] = $employee;
      }
  }
}
?>

<div class="wrapper d-flex flex-column min-vh-100 bg-light bg-opacity-50 dark:bg-transparent">
  <?php require('include/header.php'); ?>
  <div class="body flex-grow-1 px-3">
    <div class="container-lg">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <div class="fs-2 fw-semibold"><i class="fas fa-user-circle me-2"></i>Profile</div>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="dashboard.php"><i class="fas fa-home me-1"></i>Home</a></li>
              <li class="breadcrumb-item active"><span>Profile</span></li>
            </ol>
          </nav>
        </div>
        <a href="EditProfile.php" class="btn btn-primary"><i class="fas fa-edit me-1"></i>Edit Profile</a>
      </div>
      
      <!-- Profile Summary Card -->
      <div class="card mb-4">
        <div class="card-body">
          <div class="row">
            <div class="col-md-2 text-center" style="display: flex;align-items: center;justify-content: center;">
              <div class="avatar avatar-xl">
                <div style="padding: 2rem;width: 100px; height: 100px; background-color: #3c4b64; color: white; font-size: 2.5rem; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                  <?= strtoupper(substr($user['FirstName'], 0, 1)) . strtoupper(substr($user['LastName'], 0, 1)) ?>
                </div>
              </div>
            </div>
            <div class="col-md-10">
              <div class="row">
                <div class="col-md-6">
                  <h2 class="mb-0"><?= htmlspecialchars($fullName) ?></h2>
                  <p class="text-muted mb-1"><?= $desig ?> | <?= $dept ?></p>
                  <p class="mb-2"><i class="fas fa-id-badge me-2 text-primary"></i>Employee ID: <?= htmlspecialchars($user['EmpCode']) ?></p>
                  <p class="mb-2"><i class="fas fa-envelope me-2 text-primary"></i><?= htmlspecialchars($user['Email']) ?></p>
                  <p class="mb-2"><i class="fas fa-phone-alt me-2 text-primary"></i><?= htmlspecialchars($user['Number']) ?></p>
                </div>
                <div class="col-md-6">
                  <div class="d-flex justify-content-end">
                    <div class="badge bg-success p-2 me-2">
                      <i class="fas fa-calendar-check me-1"></i>
                      Joined: <?= date('d M Y', strtotime($user['JoiningDt'])) ?>
                    </div>
                    <div class="badge <?= $user['Status'] == 1 ? 'bg-success' : 'bg-danger' ?> p-2">
                      <i class="fas <?= $user['Status'] == 1 ? 'fa-check-circle' : 'fa-times-circle' ?> me-1"></i>
                      <?= $user['Status'] == 1 ? 'Active' : 'Inactive' ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <!-- Personal Information -->
        <div class="col-md-6">
          <div class="card mb-4">
            <div class="card-header bg-light">
              <strong><i class="fas fa-user me-2"></i>Personal Information</strong>
            </div>
            <div class="card-body">
              <div class="row g-3">
                <div class="col-md-6">
                  <div class="info-item">
                    <span class="text-muted"><i class="fas fa-user me-2"></i>Full Name</span>
                    <p class="mb-2"><?= htmlspecialchars($fullName) ?></p>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="info-item">
                    <span class="text-muted"><i class="fas fa-calendar-alt me-2"></i>Date of Birth</span>
                    <p class="mb-2"><?= date('d M Y', strtotime($user['DOB'])) ?></p>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="info-item">
                    <span class="text-muted"><i class="fas fa-user-friends me-2"></i>Father's Name</span>
                    <p class="mb-2"><?= htmlspecialchars($user['FatherName']) ?: 'Not provided' ?></p>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="info-item">
                    <span class="text-muted"><i class="fas fa-female me-2"></i>Mother's Name</span>
                    <p class="mb-2"><?= htmlspecialchars($user['MotherName']) ?: 'Not provided' ?></p>
                  </div>
                </div>
                <div class="col-md-12">
                  <div class="info-item">
                    <span class="text-muted"><i class="fas fa-map-marker-alt me-2"></i>Address</span>
                    <p class="mb-2"><?= htmlspecialchars($user['Address']) ?: 'Not provided' ?></p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Identity Documents -->
        <div class="col-md-6">
          <div class="card mb-4">
            <div class="card-header bg-light">
              <strong><i class="fas fa-id-card me-2"></i>Identity Documents</strong>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-12 mb-3">
                  <div class="identity-card p-3 border rounded">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                      <h5 class="mb-0"><i class="fas fa-fingerprint text-primary me-2"></i>Aadhar Card</h5>
                      <span class="badge bg-info">Primary ID</span>
                    </div>
                    <?php if (!empty($user['AadharNumber'])) { ?>
                      <p class="mb-1 identity-number">
                        <span class="font-monospace"><?= formatAadhar($user['AadharNumber']) ?></span>
                        <button class="btn btn-sm btn-link copy-btn" data-clipboard-text="<?= $user['AadharNumber'] ?>" title="Copy to clipboard">
                          <i class="fas fa-copy"></i>
                        </button>
                      </p>
                    <?php } else { ?>
                      <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>No Aadhar details provided
                      </div>
                    <?php } ?>
                  </div>
                </div>
                
                <div class="col-md-12">
                  <div class="identity-card p-3 border rounded">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                      <h5 class="mb-0"><i class="fas fa-id-card text-primary me-2"></i>PAN Card</h5>
                      <span class="badge bg-info">Tax ID</span>
                    </div>
                    <?php if (!empty($user['PancardNumber'])) { ?>
                      <p class="mb-1 identity-number">
                        <span class="font-monospace"><?= htmlspecialchars($user['PancardNumber']) ?></span>
                        <button class="btn btn-sm btn-link copy-btn" data-clipboard-text="<?= $user['PancardNumber'] ?>" title="Copy to clipboard">
                          <i class="fas fa-copy"></i>
                        </button>
                      </p>
                    <?php } else { ?>
                      <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>No PAN details provided
                      </div>
                    <?php } ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <!-- Payment Information -->
        <div class="col-md-6">
          <div class="card mb-4">
            <div class="card-header bg-light">
              <strong><i class="fas fa-money-bill-wave me-2"></i>Salary Information</strong>
            </div>
            <div class="card-body">
              <?php if ($salary) { ?>
                <div class="row">
                  <div class="col-12 mb-3">
                    <div class="p-3 bg-light rounded">
                      <div class="d-flex justify-content-between">
                        <div>
                          <h5 class="mb-0">Net Salary</h5>
                          <span class="text-muted small">Per month</span>
                        </div>
                        <div>
                          <h5 class="mb-0 text-success">₹<?= number_format($net_salary, 2) ?></h5>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                
                <h6 class="mb-3"><i class="fas fa-plus-circle text-success me-2"></i>Earnings</h6>
                <div class="table-responsive">
                  <table class="table table-sm table-borderless">
                    <tbody>
                      <tr>
                        <td>Basic Salary</td>
                        <td class="text-end">₹<?= number_format($salary['basic_salary'], 2) ?></td>
                      </tr>
                      <tr>
                        <td>HRA</td>
                        <td class="text-end">₹<?= number_format($salary['hra'], 2) ?></td>
                      </tr>
                      <tr>
                        <td>Special Allowance</td>
                        <td class="text-end">₹<?= number_format($salary['special_allowance'], 2) ?></td>
                      </tr>
                      <tr class="border-top">
                        <td><strong>Total Earnings</strong></td>
                        <td class="text-end"><strong>₹<?= number_format($total_earnings, 2) ?></strong></td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                
                <h6 class="mb-3 mt-3"><i class="fas fa-minus-circle text-danger me-2"></i>Deductions</h6>
                <div class="table-responsive">
                  <table class="table table-sm table-borderless">
                    <tbody>
                      <tr>
                        <td>PF Employee Contribution</td>
                        <td class="text-end">₹<?= number_format($salary['pf_employee_contribution'], 2) ?></td>
                      </tr>
                      <tr>
                        <td>Professional Tax</td>
                        <td class="text-end">₹<?= number_format($salary['professional_tax'], 2) ?></td>
                      </tr>
                      <tr class="border-top">
                        <td><strong>Total Deductions</strong></td>
                        <td class="text-end"><strong>₹<?= number_format($total_deductions, 2) ?></strong></td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                
                <h6 class="mb-3 mt-3"><i class="fas fa-university text-primary me-2"></i>Bank Details</h6>
                <div class="p-3 border rounded">
                  <p class="mb-1"><i class="fas fa-university me-2 text-muted"></i>Bank Name: <?= htmlspecialchars($salary['bank_name']) ?: 'Not provided' ?></p>
                  <p class="mb-1"><i class="fas fa-map-marker-alt me-2 text-muted"></i>Branch: <?= htmlspecialchars($salary['bank_branch']) ?: 'Not provided' ?></p>
                  <p class="mb-0"><i class="fas fa-credit-card me-2 text-muted"></i>Acc. No.: 
                    <?php if (!empty($salary['account_number'])) { 
                      $acc_len = strlen($salary['account_number']);
                      $masked_acc = str_repeat('X', $acc_len - 4) . substr($salary['account_number'], -4);
                      echo $masked_acc;
                    } else {
                      echo 'Not provided';
                    } ?>
                  </p>
                  <p class="mb-1"><i class="fas fa-credit-card me-2 text-muted"></i>IFSC Code: <?= htmlspecialchars($salary['ifsc_code']) ?: 'Not provided' ?></p>
                </div>
              <?php } else { ?>
                <div class="alert alert-info">
                  <i class="fas fa-info-circle me-2"></i>No salary details found. Please contact HR for more information.
                </div>
              <?php } ?>
            </div>
          </div>
        </div>

        <!-- Shift Info -->
        <div class="col-md-6">
          <div class="card mb-4">
            <div class="card-header bg-light">
              <strong><i class="fas fa-clock me-2"></i>Shift Details</strong>
            </div>
            <div class="card-body">
              <?php if ($shift) { ?>
                <div class="shift-timeline position-relative">
                  <div class="timeline-line position-absolute" style="top: 0; left: 20px; width: 2px; height: 100%; background-color: #e9ecef;"></div>
                  
                  <div class="d-flex position-relative mb-3">
                    <div class="shift-icon bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; z-index: 1;">
                      <i class="fas fa-sign-in-alt"></i>
                    </div>
                    <div class="shift-details ms-4 pt-1">
                      <h5 class="mb-0">Shift Start</h5>
                      <p class="mb-0 text-muted"><?= date('h:i A', strtotime($shift['shift_start'])) ?></p>
                    </div>
                  </div>
                  
                  <div class="d-flex position-relative mb-3">
                    <div class="shift-icon bg-warning text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; z-index: 1;">
                      <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="shift-details ms-4 pt-1">
                      <h5 class="mb-0">Late Mark Cutoff</h5>
                      <p class="mb-0 text-muted"><?= date('h:i A', strtotime($shift['late_cutoff'])) ?></p>
                    </div>
                  </div>
                  
                  <div class="d-flex position-relative">
                    <div class="shift-icon bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; z-index: 1;">
                      <i class="fas fa-sign-out-alt"></i>
                    </div>
                    <div class="shift-details ms-4 pt-1">
                      <h5 class="mb-0">Shift End</h5>
                      <p class="mb-0 text-muted"><?= date('h:i A', strtotime($shift['shift_end'])) ?></p>
                    </div>
                  </div>
                </div>
                <div class="alert alert-primary">
                  <div class="d-flex">
                    <div class="me-3">
                      <i class="fas fa-info-circle fa-2x"></i>
                    </div>
                    <div>
                      <strong>Reminder!</strong>
                      <p class="mb-0">Login before <?= date('h:i A', strtotime($shift['late_cutoff'])) ?> to avoid being marked late for the day.</p>
                    </div>
                  </div>
                </div>
              <?php } else { ?>
                <div class="text-center p-4">
                  <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                  <h5>No Shift Assigned</h5>
                  <p class="text-muted">Your shift information has not been set yet. Please contact your manager for more information.</p>
                </div>
              <?php } ?>
            </div>
          </div>
          
          <!-- Office Information -->
          <div class="card mb-4">
            <div class="card-header bg-light">
              <strong><i class="fas fa-building me-2"></i>Office Information</strong>
            </div>
            <div class="card-body">
              <div class="row g-3">
                <div class="col-md-6">
                  <div class="info-item">
                    <span class="text-muted"><i class="fas fa-phone me-2"></i>Office Phone</span>
                    <p class="mb-2"><?= htmlspecialchars($user['OfficeMobileNumber']) ?: 'Not assigned' ?></p>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="info-item">
                    <span class="text-muted"><i class="fas fa-user-tie me-2"></i>Department</span>
                    <p class="mb-2"><?= $dept ?></p>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="info-item">
                    <span class="text-muted"><i class="fas fa-id-badge me-2"></i>Designation</span>
                    <p class="mb-2"><?= $desig ?></p>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="info-item">
                    <span class="text-muted"><i class="fas fa-calendar-alt me-2"></i>Joining Date</span>
                    <p class="mb-2"><?= date('d M Y', strtotime($user['JoiningDt'])) ?></p>
                  </div>
                </div>
                <div class="col-md-12">
                  <div class="info-item">
                    <span class="text-muted"><i class="fas fa-user-friends me-2"></i>Team</span>
                    <p class="mb-2">
                      <?php
function renderUser($name, $loggedInUser, $title = '') {
    $youTag = ($name === $loggedInUser) ? ' <span class="badge you">(You)</span>' : '';
    $titleClass = '';
    if ($title === 'Manager') $titleClass = 'manager';
    else if ($title === 'Team Leader') $titleClass = 'team-leader';
    
    $titleBadge = ($title !== '') ? " <span class='badge $titleClass'>$title</span>" : '';
    return "<strong>$name</strong>$titleBadge$youTag";
}
?>

<ul class="team-tree">
  <?php foreach ($tree as $manager => $teamLeaders): ?>
    <li>
      <div class="tree-node manager">
        <i class="fas fa-user-tie"></i>
        <?php echo renderUser($manager, $_SESSION['USERNAME'], "Manager"); ?>
      </div>
      <ul>
        <?php foreach ($teamLeaders as $tl => $employees): ?>
          <li>
            <div class="tree-node team-leader">
              <i class="fas fa-user-cog"></i>
              <?php echo renderUser($tl, $_SESSION['USERNAME'], "Team Leader"); ?>
            </div>
            <ul>
              <?php foreach ($employees as $emp): ?>
                <li>
                  <div class="tree-node employee">
                    <i class="fas fa-user"></i>
                    <?php echo renderUser($emp, $_SESSION['USERNAME']); ?>
                  </div>
                </li>
              <?php endforeach; ?>
            </ul>
          </li>
        <?php endforeach; ?>
      </ul>
    </li>
  <?php endforeach; ?>
</ul>
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<style>
  ul {
  list-style-type: none;
  padding-left: 20px;
}
ul li {
  margin: 5px 0;
}
ul li strong {
  color: #34495e;
}
.team-tree {
  list-style: none;
  padding-left: 20px;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  font-size: 15px;
  color: #333;
}

.team-tree ul {
  padding-left: 20px;
  border-left: 2px solid #ddd;
  margin-left: 10px;
}

.tree-node {
  display: inline-flex;
  align-items: center;
  padding: 6px 12px;
  margin-bottom: 6px;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.08);
  background: #f9f9f9;
  transition: background-color 0.3s ease;
}

.tree-node:hover {
  background-color: #eef6ff;
}

.tree-node i {
  margin-right: 8px;
  color: #555;
  font-size: 18px;
}

.tree-node.manager {
  font-weight: 600;
  font-size: 17px;
  background: #d0e6ff;
  border-left: 5px solid #3399ff;
}

.tree-node.team-leader {
  font-weight: 500;
  font-size: 16px;
  background: #e3f2ff;
  border-left: 5px solid #66b2ff;
  margin-left: 10px;
}

.tree-node.employee {
  font-weight: 400;
  background: #f7fcff;
  border-left: 5px solid #a3c9ff;
  margin-left: 20px;
}

.badge {
  font-size: 11px;
  font-weight: 600;
  border-radius: 12px;
  padding: 3px 8px;
  margin-left: 10px;
  color: white;
  user-select: none;
}

.badge.manager {
  background-color: #007bff;
}

.badge.team-leader {
  background-color: #3399ff;
}

.badge.you {
  background-color: #28a745;
}
</style>
<!-- Add JavaScript for copy functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  const copyButtons = document.querySelectorAll('.copy-btn');
  
  copyButtons.forEach(button => {
    button.addEventListener('click', function(e) {
      e.preventDefault();
      const textToCopy = this.getAttribute('data-clipboard-text');
      navigator.clipboard.writeText(textToCopy).then(() => {
        // Change icon temporarily
        const icon = this.querySelector('i');
        icon.classList.remove('fa-copy');
        icon.classList.add('fa-check');
        
        setTimeout(() => {
          icon.classList.remove('fa-check');
          icon.classList.add('fa-copy');
        }, 2000);
      });
    });
  });
});
</script>

<?php require('include/footer.php'); ?>