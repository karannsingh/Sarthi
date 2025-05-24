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

// Process form submission
if (isset($_POST['AddUser'])) {
    // Get form data - User table
    $username = mysqli_real_escape_string($conn, $_POST['UserName']);
    $email = mysqli_real_escape_string($conn, $_POST['Email']);
    $mobile = mysqli_real_escape_string($conn, $_POST['MobileNumber']);
    
    // Generate random password
    $randomPassword = bin2hex(random_bytes(4));
    $hashedPassword = $randomPassword;
    
    $joiningDate = mysqli_real_escape_string($conn, $_POST['JoiningDate']);
    $departmentOID = (int)$_POST['DepartmentOID'];
    $designationOID = (int)$_POST['DesignationOID'];
    $ipRestricted = isset($_POST['IPRestricted']) ? 1 : 0;
    $IrisDetection = isset($_POST['IrisDetection']) ? 1 : 0;
    $status = 1; // Active by default
    
    // Get form data - User details table
    $firstName = mysqli_real_escape_string($conn, $_POST['FirstName']);
    $middleName = mysqli_real_escape_string($conn, $_POST['MiddleName']);
    $lastName = mysqli_real_escape_string($conn, $_POST['LastName']);
    $officeMobile = mysqli_real_escape_string($conn, $_POST['OfficeMobileNumber']);
    $fatherName = mysqli_real_escape_string($conn, $_POST['FatherName']);
    $motherName = mysqli_real_escape_string($conn, $_POST['MotherName']);
    $aadhar = mysqli_real_escape_string($conn, $_POST['Aadhar']);
    $pancard = mysqli_real_escape_string($conn, $_POST['Pancard']);
    $address = mysqli_real_escape_string($conn, $_POST['Address']);
    $dob = mysqli_real_escape_string($conn, $_POST['DOB']);
    
    // Calculate age
    $dobDate = new DateTime($dob);
    $now = new DateTime();
    $age = $now->diff($dobDate)->y;
    
    // Get form data - Shift details
    $shiftStart = mysqli_real_escape_string($conn, $_POST['ShiftStart']);
    $shiftEnd = mysqli_real_escape_string($conn, $_POST['ShiftEnd']);
    $lateCutoff = mysqli_real_escape_string($conn, $_POST['LateCutoff']);
    $idleTimeout = (int)$_POST['idle_timeout'];
    
    // Check if username already exists
    $checkUser = mysqli_query($conn, "SELECT UserOID FROM users WHERE UserName = '$username' AND IsDeleted = 0");
    if (mysqli_num_rows($checkUser) > 0) {
        $message = "Username already exists. Please choose a different username.";
        $messageType = "danger";
    } else {
        // Begin transaction
        mysqli_begin_transaction($conn);
        
        try {
            // Insert into users table
            $insertUser = mysqli_query($conn, "INSERT INTO users (UserName, Email, Number, Password, JoiningDt, 
                                               Designation, DepartmentOID, Status, IsDeleted, IPRestricted, IrisDetection, idle_timeout_minutes) 
                                               VALUES ('$username', '$email', '$mobile', '$hashedPassword', 
                                               '$joiningDate', '$designationOID', '$departmentOID', $status, 0, $ipRestricted, $IrisDetection, '$idleTimeout')");
            
            if (!$insertUser) {
                throw new Exception("Error inserting user: " . mysqli_error($conn));
            }
            
            $userOID = mysqli_insert_id($conn);

            // Step 3: Generate EmpCode from UserOID
            $newEmpCode = "SE" . str_pad($userOID, 3, "0", STR_PAD_LEFT);
            
            // Insert into user_details table
            $insertDetails = mysqli_query($conn, "INSERT INTO user_details 
                                                 (UserOID, EmpCode, FirstName, MiddleName, LastName, OfficeMobileNumber, 
                                                 FatherName, MotherName, AadharNumber, PancardNumber, Address, DOB, Age) 
                                                 VALUES ($userOID, '$newEmpCode', '$firstName', '$middleName', '$lastName', 
                                                 '$officeMobile', '$fatherName', '$motherName', '$aadhar', 
                                                 '$pancard', '$address', '$dob', '$age')");
            
            if (!$insertDetails) {
                throw new Exception("Error inserting user details: " . mysqli_error($conn));
            }
            
            // Insert shift information
            $insertShift = mysqli_query($conn, "INSERT INTO employee_shifts 
                                               (employee_id, shift_start, shift_end, late_cutoff) 
                                               VALUES ($userOID, '$shiftStart', '$shiftEnd', '$lateCutoff')");
            
            if (!$insertShift) {
                throw new Exception("Error inserting shift information: " . mysqli_error($conn));
            }
            
            // Commit transaction
            mysqli_commit($conn);

            $formattedStart = date("g:i A", strtotime($shiftStart));
            $formattedEnd = date("g:i A", strtotime($shiftEnd));
            $formattedCutoff = date("g:i A", strtotime($lateCutoff));
            $loginLink = "https://sarthii.co.in/InsurTech/login.php";

            $subject = "Welcome to Our Family!";
            $body = "Dear $firstName,\n\nWelcome to <strong>Sarthi Enterprises</strong>! We are delighted to have you with us.\n\nYour account has been successfully created and is now active. Below is your <strong>Credentials</strong>, Please Don't share with anyone.\n<strong>Email ID:</strong> $email\n<strong>Temporary Password:</strong> $randomPassword\nPlease <a href=\"$loginLink\" target=\"_blank\">click here</a> to login and change your password\n\n<strong>Other Details:</strong>\n<strong>Username:</strong> $username\n<strong>Contact Number:</strong> $mobile\n<strong>Joining Date:</strong> $joiningDate\n\n<strong>Shift Timing Details:</strong>\n<strong>Shift Start Time:</strong> $formattedStart\n<strong>Shift End Time:</strong> $formattedEnd\n<strong>Late Cutoff Time:</strong> $formattedCutoff\n\nIf you have any questions or need assistance, please don't hesitate to contact us.";

            // Send email (Example: use Signature 1)
            send_custom_email($email, $subject, $body, 1);
            
            $message = "User added successfully! Temporary password: $randomPassword";
            $messageType = "success";
            
            // Clear form data on success
            $_POST = array();
            
        } catch (Exception $e) {
            // Rollback on error
            mysqli_rollback($conn);
            $message = $e->getMessage();
            $messageType = "danger";
        }
    }
}

// Get all departments
$departments = mysqli_query($conn, "SELECT * FROM master_department ORDER BY DepartmentName");

// Get all designations
$designations = mysqli_query($conn, "SELECT * FROM master_designation ORDER BY DesginationLevel");
?>

<div class="wrapper d-flex flex-column min-vh-100 bg-light bg-opacity-50 dark:bg-transparent">
  <?php
  require('include/header.php');
  ?>
  <div class="body flex-grow-1 px-3">
    <div class="container-lg">
      <div class="fs-2 fw-semibold">Add Users</div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-4">
          <li class="breadcrumb-item">
            <span>Users</span>
          </li>
          <li class="breadcrumb-item active"><span>Add Users</span></li>
        </ol>
      </nav>
      
      <?php if (!empty($message)) { ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
          <?php echo $message; ?>
          <button type="button" class="btn-close" data-coreui-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php } ?>
      
      <div class="row">
        <div class="col-lg-12 text-center">
          <div class="card mb-4 p-4">
            <div class="card-title fs-4 fw-semibold"><?php echo isset($_SESSION['USERNAME']) ? 'Hi, '.$_SESSION['USERNAME'] : ''; ?></div>
            <div class="card-subtitle text-required"><?php if(isset($_SESSION['ROLE']) && $_SESSION['ROLE'] == 1) echo 'Admin'?></br>Add Users</div>
            <form method="post" autocomplete="off">
              <div class="row mb-3">
                <!-- Personal Information -->
                <div class="col-md-4 mb-3">
                  <p class="mb-0" align="left">First Name :</p>
                  <div class="input-group"><span class="input-group-text">
                    <svg class="icon">
                      <use xlink:href="assets/@coreui/icons/svg/free.svg#cil-user"></use>
                    </svg></span>
                    <input class="form-control" type="text" id="FirstName" name="FirstName" placeholder="First Name" value="<?php echo isset($_POST['FirstName']) ? htmlspecialchars($_POST['FirstName']) : ''; ?>" required>
                  </div>
                </div>
                <div class="col-md-4 mb-3">
                  <p class="mb-0" align="left">Middle Name :</p>
                  <div class="input-group"><span class="input-group-text">
                    <svg class="icon">
                      <use xlink:href="assets/@coreui/icons/svg/free.svg#cil-user"></use>
                    </svg></span>
                    <input class="form-control" type="text" id="MiddleName" name="MiddleName" placeholder="Middle Name" value="<?php echo isset($_POST['MiddleName']) ? htmlspecialchars($_POST['MiddleName']) : ''; ?>">
                  </div>
                </div>
                <div class="col-md-4 mb-3">
                  <p class="mb-0" align="left">Last Name :</p>
                  <div class="input-group"><span class="input-group-text">
                    <svg class="icon">
                      <use xlink:href="assets/@coreui/icons/svg/free.svg#cil-user"></use>
                    </svg></span>
                    <input class="form-control" type="text" id="LastName" name="LastName" placeholder="Last Name" value="<?php echo isset($_POST['LastName']) ? htmlspecialchars($_POST['LastName']) : ''; ?>" required>
                  </div>
                </div>
                <div class="col-md-4 mb-3">
                  <p class="mb-0" align="left">Father Name :</p>
                  <div class="input-group"><span class="input-group-text">
                    <svg class="icon">
                      <use xlink:href="assets/@coreui/icons/svg/free.svg#cil-user"></use>
                    </svg></span>
                    <input class="form-control" type="text" id="FatherName" name="FatherName" placeholder="Father Name" value="<?php echo isset($_POST['FatherName']) ? htmlspecialchars($_POST['FatherName']) : ''; ?>" required>
                  </div>
                </div>
                <div class="col-md-4 mb-3">
                  <p class="mb-0" align="left">Mother Name :</p>
                  <div class="input-group"><span class="input-group-text">
                    <svg class="icon">
                      <use xlink:href="assets/@coreui/icons/svg/free.svg#cil-user"></use>
                    </svg></span>
                    <input class="form-control" type="text" id="MotherName" name="MotherName" placeholder="Mother Name" value="<?php echo isset($_POST['MotherName']) ? htmlspecialchars($_POST['MotherName']) : ''; ?>" required>
                  </div>
                </div>
                
                <!-- Account Information -->
                <div class="col-md-4 mb-3">
                  <p class="mb-0" align="left">Username :</p>
                  <div class="input-group"><span class="input-group-text">
                    <svg class="icon">
                      <use xlink:href="assets/@coreui/icons/svg/free.svg#cil-user"></use>
                    </svg></span>
                    <input class="form-control" type="text" id="UserName" name="UserName" placeholder="Username" value="<?php echo isset($_POST['UserName']) ? htmlspecialchars($_POST['UserName']) : ''; ?>" required>
                  </div>
                  <div id="uname_response" class="text-danger"></div>
                </div>
                
                <!-- Contact Information -->
                <div class="col-md-4 mb-3">
                  <p class="mb-0" align="left">Mobile Number :</p>
                  <div class="input-group"><span class="input-group-text">
                    <svg class="icon">
                      <use xlink:href="assets/@coreui/icons/svg/free.svg#cil-phone"></use>
                    </svg></span>
                    <input class="form-control" type="text" id="MobileNumber" name="MobileNumber" placeholder="Mobile Number" value="<?php echo isset($_POST['MobileNumber']) ? htmlspecialchars($_POST['MobileNumber']) : ''; ?>" required oninput="validateMobileNumber(this)" maxlength="10">
                  </div>
                  <div id="MobileError" class="text-danger"></div>
                </div>
                <div class="col-md-4 mb-3">
                  <p class="mb-0" align="left">Office Mobile Number :</p>
                  <div class="input-group"><span class="input-group-text">
                    <svg class="icon">
                      <use xlink:href="assets/@coreui/icons/svg/free.svg#cil-phone"></use>
                    </svg></span>
                    <input class="form-control" type="text" id="OfficeMobileNumber" name="OfficeMobileNumber" placeholder="Office Mobile Number" value="<?php echo isset($_POST['OfficeMobileNumber']) ? htmlspecialchars($_POST['OfficeMobileNumber']) : ''; ?>" required oninput="validateOfficeMobileNumber(this)" maxlength="10">
                  </div>
                  <div id="OfficeMobileError" class="text-danger"></div>
                </div>
                <div class="col-md-4 mb-3">
                  <p class="mb-0" align="left">Aadhar Number :</p>
                  <div class="input-group"><span class="input-group-text">
                    <svg class="icon">
                      <use xlink:href="assets/@coreui/icons/svg/free.svg#cil-notes"></use>
                    </svg></span>
                    <input class="form-control" type="text" id="Aadhar" name="Aadhar" placeholder="Aadhar Number" value="<?php echo isset($_POST['Aadhar']) ? htmlspecialchars($_POST['Aadhar']) : ''; ?>" required maxlength="12" oninput="validateAadhar(this)">
                  </div>
                  <div id="AadharError" class="text-danger"></div>
                </div>
                <div class="col-md-4 mb-3">
                  <p class="mb-0" align="left">Pancard Number :</p>
                  <div class="input-group"><span class="input-group-text">
                    <svg class="icon">
                      <use xlink:href="assets/@coreui/icons/svg/free.svg#cil-notes"></use>
                    </svg></span>
                    <input class="form-control" type="text" id="Pancard" name="Pancard" placeholder="Pancard Number" value="<?php echo isset($_POST['Pancard']) ? htmlspecialchars($_POST['Pancard']) : ''; ?>" required oninput="validatePanCard(this)">
                  </div>
                  <div id="PanCardError" class="text-danger"></div>
                </div>
                <div class="col-md-4 mb-3">
                  <p class="mb-0" align="left">Email :</p>
                  <div class="input-group"><span class="input-group-text">
                    <svg class="icon">
                      <use xlink:href="assets/@coreui/icons/svg/free.svg#cil-envelope-open"></use>
                    </svg></span>
                    <input class="form-control" type="email" id="Email" name="Email" placeholder="Email" value="<?php echo isset($_POST['Email']) ? htmlspecialchars($_POST['Email']) : ''; ?>" required>
                  </div>
                </div>
                
                <!-- Dates -->
                <div class="col-md-4 mb-3">
                  <p class="mb-0" align="left">Date of Birth :</p>
                  <div class="input-group">
                    <span class="input-group-text">
                      <svg class="icon">
                        <use xlink:href="assets/@coreui/icons/svg/free.svg#cil-calendar"></use>
                      </svg>
                    </span>
                    <input class="form-control" id="DOB" name="DOB" type="date" placeholder="Date of Birthday" value="<?php echo isset($_POST['DOB']) ? htmlspecialchars($_POST['DOB']) : ''; ?>" required oninput="validateDOB(this)">
                  </div>
                  <div id="DOBError" class="text-danger"></div>
                </div>
                <div class="col-md-4 mb-3">
                  <p class="mb-0" align="left">Joining Date :</p>
                  <div class="input-group">
                    <span class="input-group-text">
                      <svg class="icon">
                        <use xlink:href="assets/@coreui/icons/svg/free.svg#cil-calendar"></use>
                      </svg>
                    </span>
                    <input class="form-control" id="JoiningDate" name="JoiningDate" type="date" placeholder="Joining Date" value="<?php echo isset($_POST['JoiningDate']) ? htmlspecialchars($_POST['JoiningDate']) : ''; ?>" required oninput="validateJoiningDate(this)">
                  </div>
                  <div id="JoiningDateError" class="text-danger"></div>
                </div>
                
                <!-- Department & Designation -->
                <div class="col-md-4 mb-3">
                  <p class="mb-0" align="left">Department :</p>
                  <div class="input-group">
                    <span class="input-group-text">
                      <svg class="icon">
                        <use xlink:href="assets/@coreui/icons/svg/free.svg#cil-layers"></use>
                      </svg>
                    </span>
                    <select class="form-select" id="DepartmentOID" name="DepartmentOID" required>
                      <option value="">-- Select Department --</option>
                      <?php while ($dept = mysqli_fetch_assoc($departments)) { ?>
                        <option value="<?php echo $dept['DepartmentOID']; ?>" <?php if(isset($_POST['DepartmentOID']) && $_POST['DepartmentOID'] == $dept['DepartmentOID']) echo 'selected'; ?>>
                          <?php echo htmlspecialchars($dept['DepartmentName']); ?>
                        </option>
                      <?php } ?>
                    </select>
                  </div>
                </div>
                <div class="col-md-4 mb-3">
                  <p class="mb-0" align="left">Designation :</p>
                  <div class="input-group">
                    <span class="input-group-text">
                      <svg class="icon">
                        <use xlink:href="assets/@coreui/icons/svg/free.svg#cil-badge"></use>
                      </svg>
                    </span>
                    <select class="form-select" id="DesignationOID" name="DesignationOID" required>
                      <option value="">-- Select Designation --</option>
                      <?php while ($desig = mysqli_fetch_assoc($designations)) { ?>
                        <option value="<?php echo $desig['DesignationOID']; ?>" <?php if(isset($_POST['DesignationOID']) && $_POST['DesignationOID'] == $desig['DesignationOID']) echo 'selected'; ?>>
                          <?php echo htmlspecialchars($desig['DesignationName']); ?>
                        </option>
                      <?php } ?>
                    </select>
                  </div>
                </div>
                
                <!-- Address -->
                <div class="col-md-4 mb-3">
                  <p class="mb-0" align="left">Address :</p>
                  <div class="input-group">
                    <span class="input-group-text">
                      <svg class="icon">
                        <use xlink:href="assets/@coreui/icons/svg/free.svg#cil-location-pin"></use>
                      </svg>
                    </span>
                    <textarea class="form-control" id="Address" name="Address" placeholder="Full Address" required><?php echo isset($_POST['Address']) ? htmlspecialchars($_POST['Address']) : ''; ?></textarea>
                  </div>
                </div>
                
                <!-- Shift Timing Information -->
                <div class="col-md-4 mb-3">
                  <p class="mb-0" align="left">Shift Start Time :</p>
                  <div class="input-group">
                    <span class="input-group-text">
                      <svg class="icon">
                        <use xlink:href="assets/@coreui/icons/svg/free.svg#cil-clock"></use>
                      </svg>
                    </span>
                    <input class="form-control" id="ShiftStart" name="ShiftStart" type="time" required value="<?php echo isset($_POST['ShiftStart']) ? htmlspecialchars($_POST['ShiftStart']) : '10:00'; ?>">
                  </div>
                </div>
                <div class="col-md-4 mb-3">
                  <p class="mb-0" align="left">Shift End Time :</p>
                  <div class="input-group">
                    <span class="input-group-text">
                      <svg class="icon">
                        <use xlink:href="assets/@coreui/icons/svg/free.svg#cil-clock"></use>
                      </svg>
                    </span>
                    <input class="form-control" id="ShiftEnd" name="ShiftEnd" type="time" required value="<?php echo isset($_POST['ShiftEnd']) ? htmlspecialchars($_POST['ShiftEnd']) : '19:00'; ?>">
                  </div>
                </div>
                <div class="col-md-4 mb-3">
                  <p class="mb-0" align="left">Late Cutoff Time :</p>
                  <div class="input-group">
                    <span class="input-group-text">
                      <svg class="icon">
                        <use xlink:href="assets/@coreui/icons/svg/free.svg#cil-clock"></use>
                      </svg>
                    </span>
                    <input class="form-control" id="LateCutoff" name="LateCutoff" type="time" required value="<?php echo isset($_POST['LateCutoff']) ? htmlspecialchars($_POST['LateCutoff']) : '10:15'; ?>">
                  </div>
                </div>

                <div class="col-md-4 mb-3">
                  <p class="mb-0" align="left">Idle Timeout (Minutes) :</p>
                  <div class="input-group">
                    <span class="input-group-text">
                      <svg class="icon">
                        <use xlink:href="assets/@coreui/icons/svg/free.svg#cil-clock"></use>
                      </svg>
                    </span>
                    <input type="number" class="form-control" id="idle_timeout" name="idle_timeout" min="1" required value="<?php echo isset($_POST['idle_timeout']) ? htmlspecialchars($_POST['idle_timeout']) : '10'; ?>">
                  </div>
                </div>
                
                <!-- IP Restriction -->
                <div class="col-md-4 mb-3">
                  <p class="mb-0" align="left">IP Restriction:</p>
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="IPRestricted" name="IPRestricted" <?php echo (isset($_POST['IPRestricted'])) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="IPRestricted">Enable IP restriction for this user</label>
                  </div>
                  <small class="text-muted">If enabled, this user will only be able to login from authorized IP addresses.</small>
                </div>

                <div class="col-md-4 mb-3">
                  <p class="mb-0" align="left">Iris Detection:</p>
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="IrisDetection" name="IrisDetection" <?php echo (isset($_POST['IrisDetection'])) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="IrisDetection">Enable Iris Detection for this user</label>
                  </div>
                  <small class="text-muted">If enabled, this user will only be able to login from authorized Iris Detection.</small>
                </div>
              </div>
              
              <button class="btn btn-block btn-success text-white" name="AddUser" type="submit" id="AddUserBtn"><i class="fa fa-save"></i> Add User</button>
              <a class="btn btn-block btn-danger text-white" href="ManageUsers.php">Cancel</a>
            </form>
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
// Form validation functions
let validationStatus = {
    mobile: true,
    officeMobile: true,
    aadhar: true,
    pancard: true,
    dob: true,
    joiningDate: true,
    username: true
};

function updateSubmitButton() {
    const submitBtn = document.getElementById("AddUserBtn");
    for (const field in validationStatus) {
        if (!validationStatus[field]) {
            submitBtn.disabled = true;
            return;
        }
    }
    submitBtn.disabled = false;
}

function validateMobileNumber(input) {
    const errorBox = document.getElementById("MobileError");
    
    // Remove any non-digit characters
    input.value = input.value.replace(/\D/g, '');

    // Check if it matches the pattern
    if (!/^[6-9]\d{9}$/.test(input.value)) {
        errorBox.innerText = "Enter valid 10-digit mobile number starting with 6-9.";
        validationStatus.mobile = false;
    } else {
        errorBox.innerText = "";
        validationStatus.mobile = true;
    }
    updateSubmitButton();
}

function validateOfficeMobileNumber(input) {
    const errorBox = document.getElementById("OfficeMobileError");
    
    // Remove any non-digit characters
    input.value = input.value.replace(/\D/g, '');

    // Check if it matches the pattern
    if (!/^[6-9]\d{9}$/.test(input.value)) {
        errorBox.innerText = "Enter valid 10-digit mobile number starting with 6-9.";
        validationStatus.officeMobile = false;
    } else {
        errorBox.innerText = "";
        validationStatus.officeMobile = true;
    }
    updateSubmitButton();
}

// Validate Aadhar Number - 12-digit number
function validateAadhar(input) {
    const errorBox = document.getElementById("AadharError");
    
    // Remove non-digit characters
    input.value = input.value.replace(/\D/g, '');

    // Validate for 12 digits
    if (input.value.length !== 12 || !/^\d{12}$/.test(input.value)) {
        errorBox.innerText = "Aadhar Number must be a 12-digit number.";
        validationStatus.aadhar = false;
    } else {
        errorBox.innerText = "";
        validationStatus.aadhar = true;
    }
    updateSubmitButton();
}

// Validate PAN Card Number - Format like XXXXX1234X
function validatePanCard(input) {
    const errorBox = document.getElementById("PanCardError");
    
    // Convert to uppercase
    input.value = input.value.toUpperCase();
    
    // Validate PAN format
    if (!/^[A-Z]{5}\d{4}[A-Z]{1}$/.test(input.value)) {
        errorBox.innerText = "Enter a valid PAN card number (e.g., ABCDE1234F).";
        validationStatus.pancard = false;
    } else {
        errorBox.innerText = "";
        validationStatus.pancard = true;
    }
    updateSubmitButton();
}

// Validate Date of Birth - Check if the user is at least 18 years old
function validateDOB(input) {
    const errorBox = document.getElementById("DOBError");

    const birthDate = new Date(input.value);
    const today = new Date();
    const age = today.getFullYear() - birthDate.getFullYear();
    const m = today.getMonth() - birthDate.getMonth();

    // If the user is not at least 18 years old
    if (age < 18 || (age === 18 && m < 0) || (age === 18 && m === 0 && today.getDate() < birthDate.getDate())) {
        errorBox.innerText = "User must be at least 18 years old.";
        validationStatus.dob = false;
    } else {
        errorBox.innerText = "";
        validationStatus.dob = true;
    }
    updateSubmitButton();
}

// Validate Joining Date - Ensure the date is not in the future
function validateJoiningDate(input) {
    const errorBox = document.getElementById("JoiningDateError");

    const joiningDate = new Date(input.value);
    const today = new Date();

    // If the joining date is in the future
    if (joiningDate > today) {
        errorBox.innerText = "Joining date cannot be a future date.";
        validationStatus.joiningDate = false;
    } else {
        errorBox.innerText = "";
        validationStatus.joiningDate = true;
    }
    updateSubmitButton();
}

// Check username availability using AJAX
document.getElementById('UserName').addEventListener('blur', function() {
    const username = this.value;
    const responseElement = document.getElementById('uname_response');
    
    if (username.length > 0) {
        // Create an AJAX request
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'check_username.php', true);
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.available) {
                    responseElement.innerHTML = '';
                    validationStatus.username = true;
                } else {
                    responseElement.innerHTML = 'Username already taken. Please choose another.';
                    validationStatus.username = false;
                }
                updateSubmitButton();
            }
        };
        xhr.send('username=' + username);
    }
});
</script>