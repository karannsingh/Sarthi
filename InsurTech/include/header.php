<!-- include/header.php -->
<header class="header header-sticky">
  <div class="container-fluid">
    <button class="header-toggler px-md-0 me-md-3 d-md-none" type="button" onclick="coreui.Sidebar.getInstance(document.querySelector('#sidebar')).toggle()">
      <svg class="icon icon-lg">
        <use xlink:href="assets/@coreui/icons/svg/free.svg#cil-menu"></use>
      </svg>
    </button>
    <a class="header-brand d-md-none" href="#">
      <!-- <svg width="118" height="46" alt="CoreUI Logo">
        <use xlink:href="assets/brand/coreui.svg#full"></use>
      </svg> -->
      <img src="assets/img/logo/SE.jpg" width="36" height="36" alt="InsurTech" class="sidebar-brand-full">
    </a>
    <ul class="header-nav ms-auto me-4">
      <?php
      if($_SESSION['ROLE'] != 1){
        require('attendance/attendance.php');
      }
      ?>
      <li class="d-flex align-items-center" style="font-weight: 600;">
        <?php echo isset($_SESSION['USERNAME']) ? 'Hi, '.$_SESSION['USERNAME'] : ''; ?>
      </li>
      <li class="nav-item dropdown d-flex align-items-center"><a class="nav-link py-0" data-coreui-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
          <div class="avatar avatar-md"><img class="avatar-img" src="assets/img/img_avatar.png" alt="user@email.com"><!-- <span class="avatar-status bg-success"> --></span></div>
        </a>
        <div class="dropdown-menu dropdown-menu-end pt-0">
          <div class="dropdown-header bg-light py-2 dark:bg-white dark:bg-opacity-10">
            <div class="fw-semibold">Settings</div>
          </div>
          <a class="dropdown-item" href="profile.php">
            <svg class="icon me-2">
              <use xlink:href="assets/@coreui/icons/svg/free.svg#cil-user"></use>
            </svg> Profile
          </a>
          <?php
          if($_SESSION['ROLE'] != 1){
            ?>
            <a class="dropdown-item " href="TimeSheetNew.php">
              <svg class="icon me-2">
                <use xlink:href="assets/@coreui/icons/svg/free.svg#cil-calendar"></use>
              </svg> Attendance
            </a>
            <?php
          }
          ?>
          <a class="dropdown-item" href="ChangePassword.php">
            <i class="fa fa-key"></i> Change Password
          </a>
          <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="logout.php">
              <svg class="icon me-2">
                <use xlink:href="assets/@coreui/icons/svg/free.svg#cil-account-logout"></use>
              </svg> Logout
            </a>
        </div>
      </li>
    </ul>
    <button class="header-toggler px-md-0 me-md-3" type="button" onclick="coreui.Sidebar.getInstance(document.querySelector('#aside')).show();" id="message">
      <svg class="icon icon-lg">
        <use xlink:href="assets/@coreui/icons/svg/free.svg#cil-applications-settings"></use>
      </svg>
    </button>
  </div>
</header>
<div class="container-fluid mini-header">
  <?php
  date_default_timezone_set('Asia/Kolkata'); // Set your local timezone
  ?>
  <marquee style="padding: 7px 5px 0px 5px" behavior="scroll" direction="left" scrollamount="4" onmouseover="this.stop();" onmouseout="this.start();">Logged-In Time: <?php echo date("h:i:s A", $_SESSION['LAST_LOGIN_TIME']); ?></marquee>
</div>