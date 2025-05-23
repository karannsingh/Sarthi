<?php
require('include/top.php');
require('include/side-navbar.php');
require('include/right-side-navbar.php');

if(isset($_SESSION['USEROID'])){
  $res=mysqli_query($conn,"select * from users U inner join user_details UD on UD.UserOID = U.UserOID where U.UserOID =".$_SESSION['USEROID']);
  $check_user=mysqli_num_rows($res);
  if($check_user>0){
    $row=mysqli_fetch_assoc($res);
    $_SESSION['USERNAME']=$row['UserName'];
    $_SESSION['USEROID']=$row['UserOID'];
    $_SESSION['ROLE']=$row['Designation'];
    $_SESSION['DEPARTMENT']=$row['DepartmentOID'];
  }else{
    echo "<script>alert('Something Went Wrong, Please Try Again!')</script>";
    echo "<script>window.location.href='logout.php';</script>";
    exit();
  }
}

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
                  <div class="card-title fs-4 fw-semibold">Your Profile</div>
                  <div class="card-subtitle text-disabled"><?php echo isset($_SESSION['USERNAME']) ? 'Hi, '.$_SESSION['USERNAME'] : ''; ?></div>
                    <form method="post" autocomplete="off">
                  <div class="row mb-3">
                    <div class="col-md-4">
                      <p class="mb-0" align="left">First Name :</p>
                      <div class="input-group"><span class="input-group-text">
                        <svg class="icon">
                          <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-user"></use>
                        </svg></span>
                        <input class="form-control" type="text" id="FirstName" name="FirstName" placeholder="First Name" value="<?php echo $row['FirstName'];?>" required>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <p class="mb-0" align="left">Middle Name :</p>
                      <div class="input-group"><span class="input-group-text">
                        <svg class="icon">
                          <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-user"></use>
                        </svg></span>
                        <input class="form-control" type="text" id="MiddleName" name="MiddleName" placeholder="Middle Name" value="<?php echo $row['MiddleName'];?>" required>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <p class="mb-0" align="left">Last Name :</p>
                      <div class="input-group"><span class="input-group-text">
                        <svg class="icon">
                          <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-user"></use>
                        </svg></span>
                        <input class="form-control" type="text" id="LastName" name="LastName" placeholder="Last Name" value="<?php echo $row['LastName'];?>" required>
                      </div>
                    </div>
                  </div>

                  <div class="row mb-3">
                    <div class="col-md-6">
                      <p class="mb-0" align="left">Username :</p>
                      <div class="input-group"><span class="input-group-text">
                        <svg class="icon">
                          <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-user"></use>
                        </svg></span>
                        <input class="form-control" type="text" id="UserName" name="UserName" placeholder="Username" value="<?php echo $row['UserName'];?>" required>
                      </div>
                      <div id="uname_response"></div>
                    </div>
                    <div class="col-md-6">
                      <p class="mb-0" align="left">Mobile Number :</p>
                      <div class="input-group"><span class="input-group-text">
                        <svg class="icon">
                          <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-phone"></use>
                        </svg></span>
                        <input class="form-control" type="text" id="MobileNumber" name="MobileNumber" placeholder="Mobile Number" value="<?php echo $row['Number'];?>" required>
                      </div>
                    </div>
                  </div>
                  <div class="row mb-3">
                    <div class="col-md-6">
                      <p class="mb-0" align="left">Aadhar Number :</p>
                      <div class="input-group"><span class="input-group-text">
                        <svg class="icon">
                          <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-notes"></use>
                        </svg></span>
                        <input class="form-control" type="text" id="Aadhar" name="Aadhar" placeholder="Aadhar Number" value="<?php echo $row['AadharNumber'];?>" required>
                      </div>
                      <div id="uname_response"></div>
                    </div>
                    <div class="col-md-6">
                      <p class="mb-0" align="left">Pancard Number :</p>
                      <div class="input-group"><span class="input-group-text">
                        <svg class="icon">
                          <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-notes"></use>
                        </svg></span>
                        <input class="form-control" type="text" id="Pancard" name="Pancard" placeholder="Pancard Number" value="<?php echo $row['PancardNumber'];?>" required>
                      </div>
                      <div id="uname_response"></div>
                    </div>
                  </div>
                  <div class="row mb-3">
                    <div class="col-md-6">
                      <p class="mb-0" align="left">Email :</p>
                      <div class="input-group"><span class="input-group-text">
                        <svg class="icon">
                          <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-envelope-open"></use>
                        </svg></span>
                        <input class="form-control" type="email" id="Email" name="Email" placeholder="Email" value="<?php echo $row['Email'];?>" required>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <p class="mb-0" align="left">Date of Birth :</p>
                      <div class="input-group">
                        <span class="input-group-text">
                          <svg class="icon">
                            <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-calendar"></use>
                          </svg>
                        </span>
                        <input class="form-control" id="DOB" name="DOB" type="date" placeholder="Date of Birthday" value="<?php echo $row['DOB'];?>" required>
                      </div>
                    </div>
                  </div>

                  <button class="btn btn-block btn-success text-white" name="submit" type="submit">Save Changes</button>
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