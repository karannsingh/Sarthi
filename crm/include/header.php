<header class="header header-sticky mb-4">
  <div class="container-fluid">
    <button class="header-toggler px-md-0 me-md-3 d-md-none" type="button" onclick="coreui.Sidebar.getInstance(document.querySelector('#sidebar')).toggle()">
      <svg class="icon icon-lg">
        <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-menu"></use>
      </svg>
    </button>
    <a class="header-brand d-md-none" href="#">
      <svg width="118" height="46" alt="CoreUI Logo">
        <use xlink:href="assets/brand/coreui.svg#full"></use>
      </svg>
    </a>
    <ul class="header-nav ms-auto me-4">
      <?php
        require('attendance/attendance.php');
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
          </div><!-- <a class="dropdown-item" href="profile.php">
            <svg class="icon me-2">
              <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-user"></use>
            </svg> Profile</a> -->
            <a class="dropdown-item" href="ChangePassword.php">
            <i class="fa fa-key"></i> Change Password</a>
            <a class="dropdown-item " href="TimeSheet.php">
              <svg class="icon me-2">
                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-calendar"></use>
              </svg> Attendance
            </a>
          <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="logout.php">
              <svg class="icon me-2">
                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-account-logout"></use>
              </svg> Logout
            </a>
        </div>
      </li>
    </ul>
    <!--<button class="header-toggler px-md-0 me-md-3" type="button" onclick="coreui.Sidebar.getInstance(document.querySelector('#aside')).show()">
      <svg class="icon icon-lg">
        <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-applications-settings"></use>
      </svg>
    </button>-->
  </div>
</header>