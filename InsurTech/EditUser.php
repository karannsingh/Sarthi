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
$userData = [];
$userDetailData = [];

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: ManageUsers.php");
    exit();
}

$userId = (int)$_GET['id'];

// Get user data
$userQuery = mysqli_query($conn, "SELECT u.*, ud.* 
                                  FROM users u 
                                  LEFT JOIN user_details ud ON u.UserOID = ud.UserOID 
                                  WHERE u.UserOID = $userId AND u.IsDeleted = 0");

if (mysqli_num_rows($userQuery) == 0) {
    header("Location: ManageUsers.php");
    exit();
}

$userData = mysqli_fetch_assoc($userQuery);

// Get all departments
$departments = mysqli_query($conn, "SELECT * FROM master_department ORDER BY DepartmentName");

// Get all designations
$designations = mysqli_query($conn, "SELECT * FROM master_designation ORDER BY DesignationName");

// Get all companies
$companies = mysqli_query($conn, "SELECT * FROM master_company WHERE IsDeleted = 0 ORDER BY CompanyName");

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate and sanitize inputs
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $mobile = mysqli_real_escape_string($conn, $_POST['mobile']);
    $departmentId = (int)$_POST['department'];
    $designation = mysqli_real_escape_string($conn, $_POST['designation']);
    $joiningDate = mysqli_real_escape_string($conn, $_POST['joining_date']);
    $status = isset($_POST['status']) ? 1 : 0;
    $ipRestricted = isset($_POST['ip_restricted']) ? 1 : 0;
    $irisDetection = isset($_POST['iris_detection']) ? 1 : 0;
    
    // Personal details
    $empCode = mysqli_real_escape_string($conn, $_POST['emp_code']);
    $firstName = mysqli_real_escape_string($conn, $_POST['first_name']);
    $middleName = mysqli_real_escape_string($conn, $_POST['middle_name']);
    $lastName = mysqli_real_escape_string($conn, $_POST['last_name']);
    $officeMobileNumber = mysqli_real_escape_string($conn, $_POST['office_mobile']);
    $fatherName = mysqli_real_escape_string($conn, $_POST['father_name']);
    $motherName = mysqli_real_escape_string($conn, $_POST['mother_name']);
    $aadharNumber = mysqli_real_escape_string($conn, $_POST['aadhar_number']);
    $pancardNumber = mysqli_real_escape_string($conn, $_POST['pancard_number']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $dob = mysqli_real_escape_string($conn, $_POST['dob']);
    $age = mysqli_real_escape_string($conn, $_POST['age']);
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format";
        $messageType = "danger";
    } 
    // Check if email already exists for other users
    else {
        $emailCheck = mysqli_query($conn, "SELECT UserOID FROM users WHERE Email = '$email' AND UserOID != $userId");
        if (mysqli_num_rows($emailCheck) > 0) {
            $message = "Email already in use by another user";
            $messageType = "danger";
        } else {
            // Start transaction
            mysqli_begin_transaction($conn);
            
            try {
                // Update users table
                $updateUser = mysqli_query($conn, "UPDATE users SET 
                    UserName = '$username',
                    Email = '$email',
                    Number = '$mobile',
                    DepartmentOID = $departmentId,
                    Designation = '$designation',
                    JoiningDt = '$joiningDate',
                    Status = $status,
                    IPRestricted = $ipRestricted,
                    IrisDetection = $irisDetection
                    WHERE UserOID = $userId");
                
                if (!$updateUser) {
                    throw new Exception(mysqli_error($conn));
                }
                
                // Check if user details record exists
                $checkUserDetails = mysqli_query($conn, "SELECT UserOID FROM user_details WHERE UserOID = $userId");
                
                if (mysqli_num_rows($checkUserDetails) > 0) {
                    // Update user_details table
                    $updateDetails = mysqli_query($conn, "UPDATE user_details SET 
                        EmpCode = '$empCode',
                        FirstName = '$firstName',
                        MiddleName = '$middleName',
                        LastName = '$lastName',
                        OfficeMobileNumber = '$officeMobileNumber',
                        FatherName = '$fatherName',
                        MotherName = '$motherName',
                        AadharNumber = '$aadharNumber',
                        PancardNumber = '$pancardNumber',
                        Address = '$address',
                        DOB = '$dob',
                        Age = '$age'
                        WHERE UserOID = $userId");
                } else {
                    // Insert into user_details table
                    $insertDetails = mysqli_query($conn, "INSERT INTO user_details 
                        (UserOID, EmpCode, FirstName, MiddleName, LastName, OfficeMobileNumber, FatherName, MotherName, 
                        AadharNumber, PancardNumber, Address, DOB, Age)
                        VALUES 
                        ($userId, '$empCode', '$firstName', '$middleName', '$lastName', '$officeMobileNumber', '$fatherName', 
                        '$motherName', '$aadharNumber', '$pancardNumber', '$address', '$dob', '$age')");
                }
                
                // Commit transaction
                mysqli_commit($conn);
                
                $message = "User information updated successfully!";
                $messageType = "success";
                
                // Refresh user data
                $userQuery = mysqli_query($conn, "SELECT u.*, ud.* 
                                                FROM users u 
                                                LEFT JOIN user_details ud ON u.UserOID = ud.UserOID 
                                                WHERE u.UserOID = $userId");
                $userData = mysqli_fetch_assoc($userQuery);
                
            } catch (Exception $e) {
                // Rollback transaction on error
                mysqli_rollback($conn);
                $message = "Error updating user: " . $e->getMessage();
                $messageType = "danger";
            }
        }
    }
}
?>

<div class="wrapper d-flex flex-column min-vh-100 bg-light bg-opacity-50 dark:bg-transparent">
  <?php require('include/header.php'); ?>
  <div class="body flex-grow-1 px-3">
    <div class="container-lg">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <div class="fs-2 fw-semibold">Edit User</div>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item">
                <span>Users</span>
              </li>
              <li class="breadcrumb-item">
                <a href="ManageUsers.php">Manage Users</a>
              </li>
              <li class="breadcrumb-item active"><span>Edit User</span></li>
            </ol>
          </nav>
        </div>
        <div>
          <a href="ManageUsers.php" class="btn btn-primary">
            <svg class="icon me-1">
              <use xlink:href="assets/@coreui/icons/svg/free.svg#cil-arrow-left"></use>
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
      
      <div class="card mb-4">
        <div class="card-header">
          <strong>Edit User Information</strong>
        </div>
        <div class="card-body">
          <form method="post" action="EditUser.php?id=<?php echo $userId; ?>">
            <div class="row">
              <div class="col-md-12 mb-4">
                <h4>Account Information</h4>
                <hr>
              </div>
              
              <div class="col-md-6 mb-3">
                <label class="form-label" for="username">Username <span class="text-danger">*</span></label>
                <input class="form-control" type="text" id="username" name="username" value="<?php echo htmlspecialchars($userData['UserName']); ?>" required>
              </div>
              
              <div class="col-md-6 mb-3">
                <label class="form-label" for="email">Email <span class="text-danger">*</span></label>
                <input class="form-control" type="email" id="email" name="email" value="<?php echo htmlspecialchars($userData['Email']); ?>" required>
              </div>
              
              <div class="col-md-6 mb-3">
                <label class="form-label" for="mobile">Mobile Number</label>
                <input class="form-control" type="text" id="mobile" name="mobile" value="<?php echo htmlspecialchars($userData['Number']); ?>">
              </div>
              
              <div class="col-md-6 mb-3">
                <label class="form-label" for="department">Department <span class="text-danger">*</span></label>
                <select class="form-select" id="department" name="department" required>
                  <option value="">Select Department</option>
                  <?php 
                  mysqli_data_seek($departments, 0);
                  while ($dept = mysqli_fetch_assoc($departments)) { 
                    $selected = ($dept['DepartmentOID'] == $userData['DepartmentOID']) ? 'selected' : '';
                  ?>
                    <option value="<?php echo $dept['DepartmentOID']; ?>" <?php echo $selected; ?>>
                      <?php echo htmlspecialchars($dept['DepartmentName']); ?>
                    </option>
                  <?php } ?>
                </select>
              </div>
              
              <div class="col-md-6 mb-3">
                <label class="form-label" for="designation">Designation <span class="text-danger">*</span></label>
                <select class="form-select" id="designation" name="designation" required>
                  <option value="">Select Designation</option>
                  <?php 
                  mysqli_data_seek($designations, 0);
                  while ($desig = mysqli_fetch_assoc($designations)) { 
                    $selected = ($desig['DesignationOID'] == $userData['Designation']) ? 'selected' : '';
                  ?>
                    <option value="<?php echo $desig['DesignationOID']; ?>" <?php echo $selected; ?>>
                      <?php echo htmlspecialchars($desig['DesignationName']); ?>
                    </option>
                  <?php } ?>
                </select>
              </div>
              
              <div class="col-md-6 mb-3">
                <label class="form-label" for="joining_date">Joining Date <span class="text-danger">*</span></label>
                <input class="form-control" type="date" id="joining_date" name="joining_date" 
                       value="<?php echo date('Y-m-d', strtotime($userData['JoiningDt'])); ?>" required>
              </div>
              
              <div class="col-md-6 mb-3">
                <div class="form-check form-switch mt-4">
                  <input class="form-check-input" type="checkbox" id="status" name="status" 
                         <?php echo ($userData['Status'] == 1) ? 'checked' : ''; ?>>
                  <label class="form-check-label" for="status">Active</label>
                </div>
              </div>
              
              <div class="col-md-6 mb-3">
                <div class="form-check form-switch mt-2">
                  <input class="form-check-input" type="checkbox" id="ip_restricted" name="ip_restricted" 
                         <?php echo ($userData['IPRestricted'] == 1) ? 'checked' : ''; ?>>
                  <label class="form-check-label" for="ip_restricted">IP Restricted</label>
                </div>
              </div>
              
              <div class="col-md-6 mb-3">
                <div class="form-check form-switch mt-2">
                  <input class="form-check-input" type="checkbox" id="iris_detection" name="iris_detection" 
                         <?php echo ($userData['IrisDetection'] == 1) ? 'checked' : ''; ?>>
                  <label class="form-check-label" for="iris_detection">Iris Detection</label>
                </div>
              </div>
              
              <div class="col-md-12 mb-4 mt-3">
                <h4>Personal Information</h4>
                <hr>
              </div>
              
              <div class="col-md-6 mb-3">
                <label class="form-label" for="emp_code">Employee Code</label>
                <input class="form-control" type="text" id="emp_code" name="emp_code" 
                       value="<?php echo htmlspecialchars($userData['EmpCode'] ?? ''); ?>">
              </div>
              
              <div class="col-md-6 mb-3">
                <label class="form-label" for="first_name">First Name <span class="text-danger">*</span></label>
                <input class="form-control" type="text" id="first_name" name="first_name" 
                       value="<?php echo htmlspecialchars($userData['FirstName'] ?? ''); ?>" required>
              </div>
              
              <div class="col-md-6 mb-3">
                <label class="form-label" for="middle_name">Middle Name</label>
                <input class="form-control" type="text" id="middle_name" name="middle_name" 
                       value="<?php echo htmlspecialchars($userData['MiddleName'] ?? ''); ?>">
              </div>
              
              <div class="col-md-6 mb-3">
                <label class="form-label" for="last_name">Last Name <span class="text-danger">*</span></label>
                <input class="form-control" type="text" id="last_name" name="last_name" 
                       value="<?php echo htmlspecialchars($userData['LastName'] ?? ''); ?>" required>
              </div>
              
              <div class="col-md-6 mb-3">
                <label class="form-label" for="office_mobile">Office Mobile Number</label>
                <input class="form-control" type="text" id="office_mobile" name="office_mobile" 
                       value="<?php echo htmlspecialchars($userData['OfficeMobileNumber'] ?? ''); ?>">
              </div>
              
              <div class="col-md-6 mb-3">
                <label class="form-label" for="father_name">Father's Name</label>
                <input class="form-control" type="text" id="father_name" name="father_name" 
                       value="<?php echo htmlspecialchars($userData['FatherName'] ?? ''); ?>">
              </div>
              
              <div class="col-md-6 mb-3">
                <label class="form-label" for="mother_name">Mother's Name</label>
                <input class="form-control" type="text" id="mother_name" name="mother_name" 
                       value="<?php echo htmlspecialchars($userData['MotherName'] ?? ''); ?>">
              </div>
              
              <div class="col-md-6 mb-3">
                <label class="form-label" for="aadhar_number">Aadhar Number</label>
                <input class="form-control" type="text" id="aadhar_number" name="aadhar_number" 
                       value="<?php echo htmlspecialchars($userData['AadharNumber'] ?? ''); ?>">
              </div>
              
              <div class="col-md-6 mb-3">
                <label class="form-label" for="pancard_number">PAN Card Number</label>
                <input class="form-control" type="text" id="pancard_number" name="pancard_number" 
                       value="<?php echo htmlspecialchars($userData['PancardNumber'] ?? ''); ?>">
              </div>
              
              <div class="col-md-6 mb-3">
                <label class="form-label" for="dob">Date of Birth</label>
                <input class="form-control" type="date" id="dob" name="dob" 
                       value="<?php echo isset($userData['DOB']) && $userData['DOB'] ? date('Y-m-d', strtotime($userData['DOB'])) : ''; ?>">
              </div>
              
              <div class="col-md-6 mb-3">
                <label class="form-label" for="age">Age</label>
                <input class="form-control" type="text" id="age" name="age" 
                       value="<?php echo htmlspecialchars($userData['Age'] ?? ''); ?>">
              </div>
              
              <div class="col-md-12 mb-3">
                <label class="form-label" for="address">Address</label>
                <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($userData['Address'] ?? ''); ?></textarea>
              </div>
              
              <div class="col-12 mt-4">
                <button class="btn btn-primary" type="submit">
                  <svg class="icon me-1">
                    <use xlink:href="assets/@coreui/icons/svg/free.svg#cil-save"></use>
                  </svg>
                  Save Changes
                </button>
                <a href="ManageUsers.php" class="btn btn-secondary ms-1">
                  <svg class="icon me-1">
                    <use xlink:href="assets/@coreui/icons/svg/free.svg#cil-x"></use>
                  </svg>
                  Cancel
                </a>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <?php require('include/footer.php'); ?>
</div>

<script>
$(document).ready(function() {
    // Calculate age automatically when DOB changes
    $('#dob').change(function() {
        var dob = new Date($(this).val());
        var today = new Date();
        var age = today.getFullYear() - dob.getFullYear();
        
        // Adjust age if birthday hasn't occurred yet this year
        if (today.getMonth() < dob.getMonth() || 
            (today.getMonth() == dob.getMonth() && today.getDate() < dob.getDate())) {
            age--;
        }
        
        if (!isNaN(age) && age > 0) {
            $('#age').val(age);
        }
    });
    
    // Form validation using jQuery validation plugin
    $('form').validate({
        rules: {
            username: {
                required: true,
                minlength: 3
            },
            email: {
                required: true,
                email: true
            },
            mobile: {
                digits: true
            },
            department: {
                required: true
            },
            designation: {
                required: true
            },
            joining_date: {
                required: true,
                date: true
            },
            first_name: {
                required: true
            },
            last_name: {
                required: true
            },
            aadhar_number: {
                minlength: 12,
                maxlength: 12
            },
            pancard_number: {
                minlength: 10,
                maxlength: 10
            }
        },
        messages: {
            username: {
                required: "Please enter a username",
                minlength: "Username must be at least 3 characters long"
            },
            email: {
                required: "Please enter an email address",
                email: "Please enter a valid email address"
            },
            department: {
                required: "Please select a department"
            },
            designation: {
                required: "Please select a designation"
            },
            joining_date: {
                required: "Please enter joining date"
            },
            first_name: {
                required: "Please enter first name"
            },
            last_name: {
                required: "Please enter last name"
            }
        },
        errorElement: 'div',
        errorPlacement: function(error, element) {
            error.addClass('invalid-feedback');
            element.closest('.col-md-6, .col-md-12').append(error);
        },
        highlight: function(element, errorClass, validClass) {
            $(element).addClass('is-invalid');
        },
        unhighlight: function(element, errorClass, validClass) {
            $(element).removeClass('is-invalid');
        },
        submitHandler: function(form) {
            form.submit();
        }
    });
});
</script>