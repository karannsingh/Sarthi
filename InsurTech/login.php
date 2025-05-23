<!-- login.php -->
<?php
require('include/top.php');

if(isset($_SESSION['LOGIN']))
{
    header("location:index.php");
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (isset($_POST['submit'])) {

  if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
      die('Invalid CSRF token');
  }
    $email = get_safe_value($conn, $_POST['username']);
    $password = get_safe_value($conn, $_POST['password']);

    $res = mysqli_query($conn, "SELECT u.UserOID, u.UserName, u.Email, u.Designation, u.DepartmentOID, u.Status, u.IPRestricted, u.idle_timeout_minutes, d.DesginationLevel, d.DesignationName, Department.DepartmentName FROM users u JOIN master_designation d ON u.Designation = d.DesignationOID JOIN master_department Department ON u.DepartmentOID = Department.DepartmentOID WHERE u.Email = '$email' AND u.Password = '$password'");
    if (mysqli_num_rows($res) > 0) {
        $row = mysqli_fetch_assoc($res);

        if ($row['Status'] == 0 && $row['Designation'] != 1) {
            echo "<script>alert('Your account is disabled, please contact to admin!');</script>";
        } else {
            $_SESSION['LOGIN'] = true;
            $_SESSION['USERNAME'] = $row['UserName'];
            $_SESSION['USEROID'] = $row['UserOID'];
            $_SESSION['ROLE'] = $row['Designation'];
            $_SESSION['DEPARTMENT'] = $row['DepartmentOID'];
            $_SESSION['LAST_ACTIVE_TIME'] = time();
            $_SESSION['USERID'] = $row['UserOID'];
            $_SESSION['EMAIL'] = $row['Email'];
            $_SESSION['ROLE_NAME'] = $row['DesignationName'];
            $_SESSION['ROLE_LEVEL'] = $row['DesginationLevel'];
            $_SESSION['DEPARTMENT_NAME'] = $row['DepartmentName'];
            $_SESSION['idle_timeout_minutes'] = $row['idle_timeout_minutes'];
            $_SESSION['LAST_LOGIN_TIME'] = time();

            echo "<script>window.location.href='index.php';</script>";
        }
    } else {
        echo "<script>alert('Wrong Credentials!');</script>";
    }
}
?>
  <div class="bg-light min-vh-100 d-flex flex-row align-items-center dark:bg-transparent">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-5">
          <div align="center" class="mb-3">
            <h1 style="font-weight: 700; color: #1a75ff;">InsurTech CRM</h1>
            <h4 class="text-light-color">Sarthi Enterprises</h4>
          </div>
          <div class="card-group d-block d-md-flex row">
            <div class="card p-4 mb-0">
              <form method="post" enctype="multipart/form-data" autocomplete="off">
              <div class="card-body text-center">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <!-- <img src="assets/img/logo/AK.png" width="60" height="56" alt="AK Insurance"> -->
                <h5 class="pb-2">Login to Your Account</h5>
                <!-- <p class="text-medium-emphasis">Sign In to your account</p> -->
                <div class="input-group mb-3 mt-4"><span class="input-group-text">
                  <svg class="icon">
                    <use xlink:href="assets/@coreui/icons/svg/free.svg#cil-user"></use>
                  </svg></span>
                  <input class="form-control" type="text" id="username" name="username" placeholder="Email ID" required>
                </div>
                <div class="input-group mb-4"><span class="input-group-text">
                  <svg class="icon">
                    <use xlink:href="assets/@coreui/icons/svg/free.svg#cil-lock-locked"></use>
                  </svg></span>
                  <input class="form-control" type="password" name="password" id="password" placeholder="Password" required>
                  <span class="input-group-text toggle-password" data-target="password">
                                            <svg class="svg-inline--fa fa-eye" id="eyeIcon" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="eye" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" width="20" height="20">
                                                <path fill="currentColor" d="M288 32c-80.8 0-145.5 36.8-192.6 80.6C48.6 156 17.3 208 2.5 243.7c-3.3 7.9-3.3 16.7 0 24.6C17.3 304 48.6 356 95.4 399.4C142.5 443.2 207.2 480 288 480s145.5-36.8 192.6-80.6c46.8-43.5 78.1-95.4 93-131.1c3.3-7.9 3.3-16.7 0-24.6c-14.9-35.7-46.2-87.7-93-131.1C433.5 68.8 368.8 32 288 32zM144 256a144 144 0 1 1 288 0 144 144 0 1 1 -288 0zm144-64c0 35.3-28.7 64-64 64c-7.1 0-13.9-1.2-20.3-3.3c-5.5-1.8-11.9 1.6-11.7 7.4c.3 6.9 1.3 13.8 3.2 20.7c13.7 51.2 66.4 81.6 117.6 67.9s81.6-66.4 67.9-117.6c-11.1-41.5-47.8-69.4-88.6-71.1c-5.8-.2-9.2 6.1-7.4 11.7c2.1 6.4 3.3 13.2 3.3 20.3z"></path>
                                            </svg>
                                        </span>
                </div>
                <div class="row justify-content-center">
                  <div class="col-6">
                    <button class="btn btn-primary px-4 text-white" type="submit" name="submit">Login</button>
                  </div>
                  <div class="col-6 text-end d-none">
                    <button class="btn btn-link px-0" type="button">Forgot password?</button>
                  </div>
                </div>
                <div class="row justify-content-center mt-3 text-light-color">
                  Unauthorized access is strictly prohibited
                </div>
              </div>
              </form>
            </div>
          </div>
          <div align="center" class="mt-3 text-light-color">
            <p>&copy; 2025 Sarthi Enterprises. All rights reserved.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- CoreUI and necessary plugins-->
  <script src="assets/@coreui/coreui-pro/js/coreui.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/js/all.min.js"></script>
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

    $(document).ready(function () {
        $(".toggle-password").click(function () {
            var inputId = $(this).attr("data-target"); 
            var input = $("#" + inputId);
            var icon = $(this).find("svg");
    
            if (input.attr("type") === "password") {
                input.attr("type", "text");
                icon.attr("data-icon", "eye-slash"); 
                icon.html('<path fill="currentColor" d="M38.8 5.1C28.4-3.1 13.3-1.2 5.1 9.2S-1.2 34.7 9.2 42.9l592 464c10.4 8.2 25.5 6.3 33.7-4.1s6.3-25.5-4.1-33.7L525.6 386.7c39.6-40.6 66.4-86.1 79.9-118.4c3.3-7.9 3.3-16.7 0-24.6c-14.9-35.7-46.2-87.7-93-131.1C465.5 68.8 400.8 32 320 32c-68.2 0-125 26.3-169.3 60.8L38.8 5.1zM223.1 149.5C248.6 126.2 282.7 112 320 112c79.5 0 144 64.5 144 144c0 24.9-6.3 48.3-17.4 68.7L408 294.5c8.4-19.3 10.6-41.4 4.8-63.3c-11.1-41.5-47.8-69.4-88.6-71.1c-5.8-.2-9.2 6.1-7.4 11.7c2.1 6.4 3.3 13.2 3.3 20.3c0 10.2-2.4 19.8-6.6 28.3l-90.3-70.8zM373 389.9c-16.4 6.5-34.3 10.1-53 10.1c-79.5 0-144-64.5-144-144c0-6.9 .5-13.6 1.4-20.2L83.1 161.5C60.3 191.2 44 220.8 34.5 243.7c-3.3 7.9-3.3 16.7 0 24.6c14.9 35.7 46.2 87.7 93 131.1C174.5 443.2 239.2 480 320 480c47.8 0 89.9-12.9 126.2-32.5L373 389.9z"></path>');
            } else {
                input.attr("type", "password");
                icon.attr("data-icon", "eye");
                icon.html('<path fill="currentColor" d="M288 32c-80.8 0-145.5 36.8-192.6 80.6C48.6 156 17.3 208 2.5 243.7c-3.3 7.9-3.3 16.7 0 24.6C17.3 304 48.6 356 95.4 399.4C142.5 443.2 207.2 480 288 480s145.5-36.8 192.6-80.6c46.8-43.5 78.1-95.4 93-131.1c3.3-7.9 3.3-16.7 0-24.6c-14.9-35.7-46.2-87.7-93-131.1C433.5 68.8 368.8 32 288 32zM144 256a144 144 0 1 1 288 0 144 144 0 1 1 -288 0zm144-64c0 35.3-28.7 64-64 64c-7.1 0-13.9-1.2-20.3-3.3c-5.5-1.8-11.9 1.6-11.7 7.4c.3 6.9 1.3 13.8 3.2 20.7c13.7 51.2 66.4 81.6 117.6 67.9s81.6-66.4 67.9-117.6c-11.1-41.5-47.8-69.4-88.6-71.1c-5.8-.2-9.2 6.1-7.4 11.7c2.1 6.4 3.3 13.2 3.3 20.3z"></path>');
            }
        });
    });
  </script>
</body>
</html>