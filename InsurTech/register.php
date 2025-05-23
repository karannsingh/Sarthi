<?php
require('include/top.php');

if(isset($_POST['submit'])){
  $FirstName=get_safe_value($conn,$_POST['FirstName']);
  $MiddleName=get_safe_value($conn,$_POST['MiddleName']);
  $LastName=get_safe_value($conn,$_POST['LastName']);
  $UserName=get_safe_value($conn,$_POST['UserName']);
  $MobileNumber=get_safe_value($conn,$_POST['MobileNumber']);
  $Email=get_safe_value($conn,$_POST['Email']);
  $DOB=get_safe_value($conn,$_POST['DOB']);
  $Password=get_safe_value($conn,$_POST['Password']);
  $CPassword=get_safe_value($conn,$_POST['CPassword']);

  if($Password === $CPassword){
    if(strlen($MobileNumber) == 10){
      $check_user=mysqli_num_rows(mysqli_query($conn,"select * from users where UserName='$UserName'"));
      if($check_user>0){
        echo "<script>alert('User Name already registered!')</script>";
      }else{
        $check_user=mysqli_num_rows(mysqli_query($conn,"select * from users where Email='$Email'"));
        if($check_user>0){
          echo "<script>alert('Email already registered!')</script>";
        }else{
          $check_user=mysqli_num_rows(mysqli_query($conn,"select * from users where Number='$MobileNumber'"));
          if($check_user>0){
            echo "<script>alert('Mobile Number already registered!')</script>";
          }else{
            mysqli_query($conn,"INSERT INTO `users`(`UserName`, `Email`, `Number`, `Password`, `JoiningDt`, `Designation`, `Status`, `IsDeleted`) VALUES('$UserName','$Email','$MobileNumber','$Password','','4',0,0)");

            $query = "INSERT INTO `users`(`UserName`, `Email`, `Number`, `Password`, `JoiningDt`, `Designation`, `Status`, `IsDeleted`) VALUES('$UserName','$Email','$MobileNumber','$Password','','4',0,0)";

            if (mysqli_query($conn, $query)) {
              $lastInsertedId = mysqli_insert_id($conn);
              mysqli_query($conn,"INSERT INTO `user_details`(`UserOID`, `FirstName`, `MiddleName`, `LastName`,`DOB`, `Age`) VALUES ('$lastInsertedId','$FirstName','$MiddleName','$LastName','$DOB','')");
              echo "<script>alert('User Successfully Registered!')</script>";
            } else {
              echo "Error: " . mysqli_error($conn);
            }
            ?>
            <script>
              window.location.href='login.php';
            </script>
            <?php 
          }
        }
      }
    }else{
      echo "<script>alert('Please provide valid number!')</script>";
    }
  }else{
    echo "<script>alert('Password and Confirm Password not matched!')</script>";
  }
}
?>
    <div class="bg-light min-vh-100 d-flex flex-row align-items-center dark:bg-transparent">
      <div class="container">
        <div class="row justify-content-center" align="center">
          <div class="col-md-8">
            <div class="card mb-4 mx-4">
              <div class="card-body p-4">
                <h1 class="mb-3">Register</h1>
                <form method="post" autocomplete="off">
                  <div class="row mb-3">
                    <div class="col-md-4">
                      <p class="mb-0" align="left">First Name :</p>
                      <div class="input-group"><span class="input-group-text">
                        <svg class="icon">
                          <use xlink:href="assetss/@coreui/icons/svg/free.svg#cil-user"></use>
                        </svg></span>
                        <input class="form-control" type="text" id="FirstName" name="FirstName" placeholder="First Name" required>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <p class="mb-0" align="left">Middle Name :</p>
                      <div class="input-group"><span class="input-group-text">
                        <svg class="icon">
                          <use xlink:href="assetss/@coreui/icons/svg/free.svg#cil-user"></use>
                        </svg></span>
                        <input class="form-control" type="text" id="MiddleName" name="MiddleName" placeholder="Middle Name" required>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <p class="mb-0" align="left">Last Name :</p>
                      <div class="input-group"><span class="input-group-text">
                        <svg class="icon">
                          <use xlink:href="assetss/@coreui/icons/svg/free.svg#cil-user"></use>
                        </svg></span>
                        <input class="form-control" type="text" id="LastName" name="LastName" placeholder="Last Name" required>
                      </div>
                    </div>
                  </div>

                  <div class="row mb-3">
                    <div class="col-md-6">
                      <p class="mb-0" align="left">Username :</p>
                      <div class="input-group"><span class="input-group-text">
                        <svg class="icon">
                          <use xlink:href="assetss/@coreui/icons/svg/free.svg#cil-user"></use>
                        </svg></span>
                        <input class="form-control" type="text" id="UserName" name="UserName" placeholder="Username" required>
                      </div>
                      <div id="uname_response"></div>
                    </div>
                    <div class="col-md-6">
                      <p class="mb-0" align="left">Mobile Number :</p>
                      <div class="input-group"><span class="input-group-text">
                        <svg class="icon">
                          <use xlink:href="assetss/@coreui/icons/svg/free.svg#cil-phone"></use>
                        </svg></span>
                        <input class="form-control" type="text" id="MobileNumber" name="MobileNumber" placeholder="Mobile Number" required>
                      </div>
                    </div>
                  </div>

                  <div class="row mb-3">
                    <div class="col-md-6">
                      <p class="mb-0" align="left">Email :</p>
                      <div class="input-group"><span class="input-group-text">
                        <svg class="icon">
                          <use xlink:href="assetss/@coreui/icons/svg/free.svg#cil-envelope-open"></use>
                        </svg></span>
                        <input class="form-control" type="email" id="Email" name="Email" placeholder="Email" required>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <p class="mb-0" align="left">Date of Birth :</p>
                      <div class="input-group">
                        <span class="input-group-text">
                          <svg class="icon">
                            <use xlink:href="assetss/@coreui/icons/svg/free.svg#cil-calendar"></use>
                          </svg>
                        </span>
                        <input class="form-control" id="DOB" name="DOB" type="date" placeholder="Date of Birthday" required>
                      </div>
                    </div>
                  </div>

                  <div class="row mb-3">
                    <div class="col-md-6">
                      <p class="mb-0" align="left">Password :</p>
                      <div class="input-group"><span class="input-group-text">
                        <svg class="icon">
                          <use xlink:href="assetss/@coreui/icons/svg/free.svg#cil-lock-locked"></use>
                        </svg></span>
                        <input class="form-control" id="Password" name="Password" type="password" placeholder="Password" required>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <p class="mb-0" align="left">Confirm Password :</p>
                      <div class="input-group"><span class="input-group-text">
                        <svg class="icon">
                          <use xlink:href="assetss/@coreui/icons/svg/free.svg#cil-lock-locked"></use>
                        </svg></span>
                        <input class="form-control" id="CPassword" name="CPassword" type="password" placeholder="Confirm Password" required>
                      </div>  
                    </div>
                  </div>
                  <button class="btn btn-block btn-success text-white" name="submit" type="submit">Create Account</button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- CoreUI and necessary plugins-->
    <script src="assetss/@coreui/coreui-pro/js/coreui.bundle.min.js"></script>
    <script src="assetss/simplebar/js/simplebar.min.js"></script>
    <script>
      if (document.body.classList.contains('dark-theme')) {
        var element = document.getElementById('btn-dark-theme');
        if (typeof(element) != 'undefined' && element != null) {
          document.getElementById('btn-dark-theme').checked = true;
        }
      } else {
        var element = document.getElementById('btn-light-theme');
        if (typeof(element) != 'undefined' && element != null) {
          document.getElementById('btn-light-theme').checked = true;
        }
      }

      function handleThemeChange(src) {
        var event = document.createEvent('Event');
        event.initEvent('themeChange', true, true);

        if (src.value === 'light') {
          document.body.classList.remove('dark-theme');
        }
        if (src.value === 'dark') {
          document.body.classList.add('dark-theme');
        }
        document.body.dispatchEvent(event);
      }
    </script>
    <script type="text/javascript">
      
      $(document).ready(function(){
        $(document).on("keyup", "#UserName", function () {
          alert("Hello");
        });
       $("#UserName").keyup(function(){
        
         var username = $(this).val().trim();
         if(username != ''){
            $("#uname_response").show();
            $.ajax({
               url: 'include/CheckUserName.php',
               type: 'post',
               data: {username:username},
               success: function(response){
                  // Show response
                  $("#uname_response").html(response);
               }
            });
         }else{
            $("#uname_response").hide();
         }
      });
    </script>
  </body>
</html>