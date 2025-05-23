<?php
require('include/top.php');

date_default_timezone_set('Asia/Kolkata');

if(isset($_SESSION['LOGIN']))
{
    header("location:index.php");
}

if(isset($_POST['submit'])){
  $email=get_safe_value($conn,$_POST['username']);
  $password=get_safe_value($conn,$_POST['password']);

  $res=mysqli_query($conn,"select * from users where Email='$email' and Password='$password'");
  $check_user=mysqli_num_rows($res);
  if($check_user>0){
    $row=mysqli_fetch_assoc($res);
    if ($row['Status']==1) {
      echo "<script>alert('Your account is disabled, please contact to admin!')</script>";
    }else{
      $_SESSION['LOGIN']='yes';
      $_SESSION['USERNAME']=$row['UserName'];
      $_SESSION['USEROID']=$row['UserOID'];
      $_SESSION['ROLE']=$row['Designation'];
      $_SESSION['DEPARTMENT']=$row['DepartmentOID'];
      $current_time = date("Y-m-d H:i:s");  
      $_SESSION['LAST_ACTIVE_TIME'] = time();
      
      echo "<script>window.location.href='index.php';</script>";
    }
  }else{
    echo "<script>alert('Wrong Credentials!')</script>";
  }
}
?>
  <div class="bg-light min-vh-100 d-flex flex-row align-items-center dark:bg-transparent">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-6">
          <div class="card-group d-block d-md-flex row">
            <div class="card p-4 mb-0">
              <form method="post" enctype="multipart/form-data" autocomplete="off">
              <div class="card-body text-center">
                <img src="assets/img/logo/AK.png" width="60" height="56" alt="AK Insurance">
                <h3>Login</h3>
                <!-- <p class="text-medium-emphasis">Sign In to your account</p> -->
                <div class="input-group mb-3 mt-3"><span class="input-group-text">
                  <svg class="icon">
                    <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-user"></use>
                  </svg></span>
                  <input class="form-control" type="text" id="username" name="username" placeholder="Username" required>
                </div>
                <div class="input-group mb-4"><span class="input-group-text">
                  <svg class="icon">
                    <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-lock-locked"></use>
                  </svg></span>
                  <input class="form-control" type="password" name="password" id="password" placeholder="Password" required>
                </div>
                <div class="row justify-content-center">
                  <div class="col-6">
                    <button class="btn btn-primary px-4 text-white" type="submit" name="submit">Login</button>
                  </div>
                  <div class="col-6 text-end d-none">
                    <button class="btn btn-link px-0" type="button">Forgot password?</button>
                  </div>
                </div>
              </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- CoreUI and necessary plugins-->
  <script src="vendors/@coreui/coreui-pro/js/coreui.bundle.min.js"></script>
  <script src="vendors/simplebar/js/simplebar.min.js"></script>
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
  <script>
  </script>

</body>
</html>