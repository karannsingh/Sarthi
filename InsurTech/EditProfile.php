<?php
require('include/top.php');
require('include/side-navbar.php');
require('include/right-side-navbar.php');
?>
<div class="wrapper d-flex flex-column min-vh-100 bg-light bg-opacity-50 dark:bg-transparent">
  <?php
  require('include/header.php');
  ?>
  <div class="body flex-grow-1 px-3">
    <div class="container-lg">
      <div class="fs-2 fw-semibold">Profile</div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-4">
          <li class="breadcrumb-item">
<!-- if breadcrumb is single--><span>Home</span>
</li>
<li class="breadcrumb-item active"><span>Profile</span></li>
</ol>
</nav>
<div class="row">
  <div class="col-lg-12 text-center">
    <div class="card mb-4">
      <div class="card-body p-4">
        <div class="card-title fs-4 fw-semibold"><?php echo isset($_SESSION['USERNAME']) ? 'Hi, '.$_SESSION['USERNAME'] : ''; ?></div>
        <div class="card-subtitle text-disabled">Your Profile</div>
        <?php 
        $row = null;
        if(isset($_SESSION['USEROID'])){
          $res = mysqli_query($conn,"SELECT * FROM users U LEFT OUTER JOIN user_details UD ON UD.UserOID = U.UserOID  LEFT OUTER JOIN master_designation UserDesignation ON UserDesignation.DesignationOID = U.Designation LEFT OUTER JOIN master_department UserDepartment ON UserDepartment.DepartmentOID = U.DepartmentOID WHERE U.UserOID = ".intval($_SESSION['USEROID']));
          if($res && mysqli_num_rows($res) > 0){
            $row = mysqli_fetch_assoc($res);
            $_SESSION['USERNAME'] = $row['UserName'];
            $_SESSION['USEROID']  = $row['UserOID'];
            $_SESSION['ROLE']     = $row['Designation'];
            $_SESSION['DEPARTMENT'] = $row['DepartmentOID'];
          } else {
            echo "<script>alert('Something Went Wrong, Please Try Again!')</script>";
            echo "<script>window.location.href='logout.php';</script>";
            exit();
          }
        } else {
          echo "<script>alert('Session expired! Please login again.')</script>";
          echo "<script>window.location.href='logout.php';</script>";
          exit();
        }
        ?>
        <form method="post" autocomplete="off" action="function/update_profile.php">
          <div class="row mb-3">
            <?php
            if($_SESSION['ROLE'] != 1){
              ?>
            <div class="col-md-4 mb-3">
              <p class="mb-0" align="left">First Name :</p>
              <div class="input-group"><span class="input-group-text">
                <svg class="icon">
                  <use xlink:href="vendor/@coreui/icons/svg/free.svg#cil-user"></use>
                </svg></span>
                <input class="form-control" type="text" id="FirstName" name="FirstName" placeholder="First Name" value="<?php echo htmlspecialchars($row['FirstName']); ?>" required>
              </div>
            </div>
            <div class="col-md-4 mb-3">
              <p class="mb-0" align="left">Middle Name :</p>
              <div class="input-group"><span class="input-group-text">
                <svg class="icon">
                  <use xlink:href="vendor/@coreui/icons/svg/free.svg#cil-user"></use>
                </svg></span>
                <input class="form-control" type="text" id="MiddleName" name="MiddleName" placeholder="Middle Name" value="<?php echo $row['MiddleName'];?>" required>
              </div>
            </div>
            <div class="col-md-4 mb-3">
              <p class="mb-0" align="left">Last Name :</p>
              <div class="input-group"><span class="input-group-text">
                <svg class="icon">
                  <use xlink:href="vendor/@coreui/icons/svg/free.svg#cil-user"></use>
                </svg></span>
                <input class="form-control" type="text" id="LastName" name="LastName" placeholder="Last Name" value="<?php echo $row['LastName'];?>" required>
              </div>
            </div>
            <div class="col-md-4 mb-3">
              <p class="mb-0" align="left">Employee Code ID :</p>
              <div class="input-group"><span class="input-group-text">
                <svg class="icon">
                  <use xlink:href="vendor/@coreui/icons/svg/free.svg#cil-user"></use>
                </svg></span>
                <input class="form-control" type="text" id="EmpCode" name="EmpCode" placeholder="EmpCode" value="<?php echo $row['EmpCode'];?>" disabled>
              </div>
              <div id="uname_response"></div>
            </div>
            <div class="col-md-4 mb-3">
              <p class="mb-0" align="left">Father Name :</p>
              <div class="input-group"><span class="input-group-text">
                <svg class="icon">
                  <use xlink:href="vendor/@coreui/icons/svg/free.svg#cil-user"></use>
                </svg></span>
                <input class="form-control" type="text" id="FatherName" name="FatherName" placeholder="Father Name" value="<?php echo $row['FatherName'];?>" disabled>
              </div>
              <div id="uname_response"></div>
            </div>
            <div class="col-md-4 mb-3">
              <p class="mb-0" align="left">Mother Name :</p>
              <div class="input-group"><span class="input-group-text">
                <svg class="icon">
                  <use xlink:href="vendor/@coreui/icons/svg/free.svg#cil-user"></use>
                </svg></span>
                <input class="form-control" type="text" id="MotherName" name="MotherName" placeholder="Mother Name" value="<?php echo $row['MotherName'];?>" disabled>
              </div>
              <div id="uname_response"></div>
            </div>
            <div class="col-md-4 mb-3">
              <p class="mb-0" align="left">Username :</p>
              <div class="input-group"><span class="input-group-text">
                <svg class="icon">
                  <use xlink:href="vendor/@coreui/icons/svg/free.svg#cil-user"></use>
                </svg></span>
                <input class="form-control" type="text" id="UserName" name="UserName" placeholder="Username" value="<?php echo $row['UserName'];?>" required>
              </div>
              <div id="uname_response"></div>
            </div>
            <div class="col-md-4 mb-3">
              <p class="mb-0" align="left">Mobile Number :</p>
              <div class="input-group"><span class="input-group-text">
                <svg class="icon">
                  <use xlink:href="vendor/@coreui/icons/svg/free.svg#cil-phone"></use>
                </svg></span>
                <input class="form-control" type="text" id="MobileNumber" name="MobileNumber" placeholder="Mobile Number" value="<?php echo $row['Number'];?>" disabled>
              </div>
            </div>
            <div class="col-md-4 mb-3">
              <p class="mb-0" align="left">Office Mobile Number :</p>
              <div class="input-group"><span class="input-group-text">
                <svg class="icon">
                  <use xlink:href="vendor/@coreui/icons/svg/free.svg#cil-phone"></use>
                </svg></span>
                <input class="form-control" type="text" id="OfficeMobileNumber" name="OfficeMobileNumber" placeholder="Office Mobile Number" maxlength="10"
                 value="<?php echo $row['OfficeMobileNumber'];?>" required oninput="validateMobileNumber(this)">
                <small id="mobileError" class="text-danger"></small>
              </div>
            </div>
            <div class="col-md-4 mb-3">
              <p class="mb-0" align="left">Aadhar Number :</p>
              <div class="input-group"><span class="input-group-text">
                <svg class="icon">
                  <use xlink:href="vendor/@coreui/icons/svg/free.svg#cil-notes"></use>
                </svg></span>
                <input class="form-control" type="text" id="Aadhar" name="Aadhar" placeholder="Aadhar Number" value="<?php echo $row['AadharNumber'];?>" disabled>
              </div>
              <div id="uname_response"></div>
            </div>
            <div class="col-md-4 mb-3">
              <p class="mb-0" align="left">Pancard Number :</p>
              <div class="input-group"><span class="input-group-text">
                <svg class="icon">
                  <use xlink:href="vendor/@coreui/icons/svg/free.svg#cil-notes"></use>
                </svg></span>
                <input class="form-control" type="text" id="Pancard" name="Pancard" placeholder="Pancard Number" value="<?php echo $row['PancardNumber'];?>" disabled>
              </div>
              <div id="uname_response"></div>
            </div>
            <div class="col-md-4 mb-3">
              <p class="mb-0" align="left">Email :</p>
              <div class="input-group"><span class="input-group-text">
                <svg class="icon">
                  <use xlink:href="vendor/@coreui/icons/svg/free.svg#cil-envelope-open"></use>
                </svg></span>
                <input class="form-control" type="email" id="Email" name="Email" placeholder="Email" value="<?php echo $row['Email'];?>" disabled>
              </div>
            </div>
            <div class="col-md-4 mb-3">
              <p class="mb-0" align="left">Date of Birth :</p>
              <div class="input-group">
                <span class="input-group-text">
                  <svg class="icon">
                    <use xlink:href="vendor/@coreui/icons/svg/free.svg#cil-calendar"></use>
                  </svg>
                </span>
                <input class="form-control" id="DOB" name="DOB" type="date" placeholder="Date of Birthday" value="<?php echo $row['DOB'];?>" disabled>
              </div>
            </div>
            <div class="col-md-4 mb-3">
              <p class="mb-0" align="left">Joining Date :</p>
              <div class="input-group">
                <span class="input-group-text">
                  <svg class="icon">
                    <use xlink:href="vendor/@coreui/icons/svg/free.svg#cil-calendar"></use>
                  </svg>
                </span>
                <input class="form-control" id="JoiningDate" name="JoiningDate" type="date" placeholder="Joining Date" value="<?php echo $row['JoiningDt'];?>" disabled>
              </div>
            </div>
              <div class="col-md-4 mb-3">
              <p class="mb-0" align="left">Department :</p>
              <div class="input-group">
                <span class="input-group-text">
                  <svg class="icon">
                    <use xlink:href="vendor/@coreui/icons/svg/free.svg#cil-notes"></use>
                  </svg>
                </span>
                <input class="form-control" id="Department" name="Department" type="text" placeholder="Joining Date" value="<?php echo $row['DepartmentName'];?>" disabled>
              </div>
            </div>
              <div class="col-md-4 mb-3">
                <p class="mb-0" align="left">Designation :</p>
                <div class="input-group">
                  <span class="input-group-text">
                    <svg class="icon">
                      <use xlink:href="vendor/@coreui/icons/svg/free.svg#cil-notes"></use>
                    </svg>
                  </span>
                  <input class="form-control" id="Designation" name="Designation" type="text" placeholder="Designation" value="<?php echo $row['DesignationName'];?>" disabled>
                </div>
              </div>
              <div class="col-md-4 mb-3">
                <p class="mb-0" align="left">Address :</p>
                <div class="input-group">
                  <span class="input-group-text">
                    <svg class="icon">
                      <use xlink:href="vendor/@coreui/icons/svg/free.svg#cil-location-pin"></use>
                    </svg>
                  </span>
                  <textarea class="form-control" id="Address" name="Address" type="text" placeholder="Address" disabled><?php echo $row['Address'];?></textarea>
                </div>
              </div>
              <?php
            }else{
              ?>
              <div class="col-md-4 mb-3">
              <p class="mb-0" align="left">Employee Code ID :</p>
              <div class="input-group"><span class="input-group-text">
                <svg class="icon">
                  <use xlink:href="vendor/@coreui/icons/svg/free.svg#cil-user"></use>
                </svg></span>
                <input class="form-control" type="text" id="EmpCode" name="EmpCode" placeholder="EmpCode" value="<?php echo $row['EmpCode'];?>" disabled>
              </div>
              <div id="uname_response"></div>
            </div>
            <div class="col-md-4 mb-3">
              <p class="mb-0" align="left">Username :</p>
              <div class="input-group"><span class="input-group-text">
                <svg class="icon">
                  <use xlink:href="vendor/@coreui/icons/svg/free.svg#cil-user"></use>
                </svg></span>
                <input class="form-control" type="text" id="UserName" name="UserName" placeholder="Username" value="<?php echo $row['UserName'];?>" required>
              </div>
              <div id="uname_response"></div>
            </div>
            <div class="col-md-4 mb-3">
              <p class="mb-0" align="left">Mobile Number :</p>
              <div class="input-group"><span class="input-group-text">
                <svg class="icon">
                  <use xlink:href="vendor/@coreui/icons/svg/free.svg#cil-phone"></use>
                </svg></span>
                <input class="form-control" type="text" id="MobileNumber" name="MobileNumber" placeholder="Mobile Number" value="<?php echo $row['Number'];?>" disabled>
              </div>
            </div>
            <div class="col-md-4 mb-3">
              <p class="mb-0" align="left">Office Mobile Number :</p>
              <div class="input-group"><span class="input-group-text">
                <svg class="icon">
                  <use xlink:href="vendor/@coreui/icons/svg/free.svg#cil-phone"></use>
                </svg></span>
                <input class="form-control" type="text" id="OfficeMobileNumber" name="OfficeMobileNumber" placeholder="Office Mobile Number" maxlength="10"
                 value="<?php echo $row['OfficeMobileNumber'];?>" required oninput="validateMobileNumber(this)">
                <small id="mobileError" class="text-danger"></small>
              </div>
            </div>
            <div class="col-md-4 mb-3">
              <p class="mb-0" align="left">Email :</p>
              <div class="input-group"><span class="input-group-text">
                <svg class="icon">
                  <use xlink:href="vendor/@coreui/icons/svg/free.svg#cil-envelope-open"></use>
                </svg></span>
                <input class="form-control" type="email" id="Email" name="Email" placeholder="Email" value="<?php echo $row['Email'];?>" disabled>
              </div>
            </div>
            <div class="col-md-4 mb-3">
                <p class="mb-0" align="left">Designation :</p>
                <div class="input-group">
                  <span class="input-group-text">
                    <svg class="icon">
                      <use xlink:href="vendor/@coreui/icons/svg/free.svg#cil-notes"></use>
                    </svg>
                  </span>
                  <input class="form-control" id="Designation" name="Designation" type="text" placeholder="Designation" value="<?php echo $row['DesignationName'];?>" disabled>
                </div>
              </div>
              <?php
            }
            ?>
            </div>
              <button class="btn btn-block btn-success text-white" name="update_profile" type="submit" id="updateBtn"><i class="fa fa-save"></i> Update Profile</button>
              <a class="btn btn-block btn-danger text-white" href="profile.php">Cancel</a>
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
function validateMobileNumber(input) {
    const errorBox = document.getElementById("mobileError");
    const submitBtn = document.getElementById("updateBtn");
    
    // Remove any non-digit characters
    input.value = input.value.replace(/\D/g, '');

    // Check if it matches the pattern
    if (!/^[6-9]\d{9}$/.test(input.value)) {
        errorBox.innerText = "Enter valid 10-digit mobile number starting with 6-9.";
        submitBtn.disabled = true;
    } else {
        errorBox.innerText = "";
        submitBtn.disabled = false;
    }
}
</script>