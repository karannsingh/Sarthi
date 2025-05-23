<?php
  if(!isset($_SESSION['LOGIN']))
  {
      header("location:logout.php");
  }
?>
<div class="sidebar sidebar-dark sidebar-fixed bg-dark-gradient" id="sidebar">
      <div class="sidebar-brand d-none d-md-flex">
        <img src="assets/img/logo/AK.png" width="56" height="46" alt="AK Insurance" class="sidebar-brand-full">
        <img src="assets/img/logo/AK.png" width="46" height="46" alt="AK Insurance" class="sidebar-brand-narrow">
        <button class="sidebar-toggler" type="button" data-coreui-toggle="unfoldable"></button>
      </div>
      <ul class="sidebar-nav" data-coreui="navigation" data-simplebar="">
        <li class="nav-item"><a class="nav-link" href="index.php">
            <svg class="nav-icon">
              <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-speedometer"></use>
            </svg> Dashboard</a></li>
        <!-- <li class="nav-title">Components</li>
        <li class="nav-group"><a class="nav-link nav-group-toggle" href="#">
            <svg class="nav-icon">
              <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-puzzle"></use>
            </svg>Leads</a>
          <ul class="nav-group-items">
            <li class="nav-item"><a class="nav-link" href="AddLead.php"><span class="nav-icon"></span>Add Lead</a></li>
            <li class="nav-item"><a class="nav-link" href="LeadSummary.php"><span class="nav-icon"></span>Lead Summary</a></li>
          </ul>
        </li>
        <li class="nav-group"><a class="nav-link nav-group-toggle" href="#">
            <svg class="nav-icon">
              <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-notes"></use>
            </svg>Purchased</a>
          <ul class="nav-group-items">
            <li class="nav-item"><a class="nav-link" href="forms/form-control.html">Form Control</a></li>
            <li class="nav-item"><a class="nav-link" href="forms/select.html">Select</a></li>
          </ul>
        </li>
        <li class="nav-group"><a class="nav-link nav-group-toggle" href="#">
            <svg class="nav-icon">
              <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-bell"></use>
            </svg> Follow Up</a>
          <ul class="nav-group-items">
            <li class="nav-item"><a class="nav-link" href="notifications/alerts.html"><span class="nav-icon"></span> All Follow Up</a></li>
            <li class="nav-item"><a class="nav-link" href="notifications/badge.html"><span class="nav-icon"></span> Today Follow Up</a></li>
          </ul>
        </li> -->
      </ul>
    </div>