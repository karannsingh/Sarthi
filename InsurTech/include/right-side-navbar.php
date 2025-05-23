<!-- include/right-side-navbar.php -->
<div class="sidebar sidebar-light sidebar-lg sidebar-end sidebar-overlaid hide" id="aside">
  <div class="sidebar-header bg-transparent p-0">
    <ul class="nav nav-underline nav-underline-primary" role="tablist" style="padding-left: 1.5rem;">
      <li class="nav-item">
        <a class="nav-link active" style="padding-left: 1rem !important;padding-right: 1rem !important;" data-coreui-toggle="tab" href="#messages" role="tab">
          <svg class="icon">
            <use xlink:href="assets/@coreui/icons/svg/free.svg#cil-speech"></use>
          </svg>
        </a>
      </li>
    </ul>
    <button class="sidebar-close" type="button" data-coreui-close="sidebar">
      <svg class="icon">
        <use xlink:href="assets/@coreui/icons/svg/free.svg#cil-x"></use>
      </svg>
    </button>
  </div>
  
  <!-- Tab content -->
  <div class="tab-content" style="overflow-x: hidden;">
    <div class="tab-pane active p-2" id="messages" role="tabpanel">
      <!-- Access Log Alert -->
      <div id="access-alert" class="alert alert-dismissible fade show d-none" role="alert">
        <div id="access-message"></div>
        <button type="button" class="btn-close" data-coreui-dismiss="alert" aria-label="Close"></button>
      </div>
      
      <!-- Status Bar with auto-refresh timer -->
      <div id="refresh-status" class="mb-2 small text-muted d-flex align-items-center justify-content-between border-bottom pb-2">
        <span>Last refresh: <span id="last-refresh-time">Never</span></span>
        <span>Auto refresh in: <span id="next-refresh-time">30s</span></span>
      </div>
      
      <!-- PolicyBazaar OTP Section -->
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="mb-0 fw-semibold text-primary d-flex align-items-center gap-2">
          <i class="fa fa-shield-alt"></i> PolicyBazaar OTPs
        </h6>
        <button class="btn btn-sm btn-outline-primary py-1" id="refresh-pb-btn" onclick="refreshOtpManually('pb'); return false;">
          <i class="fa fa-sync-alt"></i> Refresh
        </button>
      </div>
      <div id="pb-otp-container" class="d-flex flex-column gap-1 mb-3">
        <!-- OTPs will render here -->
        <div class="text-center text-muted small">Loading OTPs...</div>
      </div>

      <!-- ICICI Lombard OTP Section -->
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="mb-0 fw-semibold text-danger d-flex align-items-center gap-2">
          <i class="fa fa-umbrella"></i> ICICI Lombard OTPs
        </h6>
        <button class="btn btn-sm btn-outline-danger py-1" id="refresh-icici-btn" onclick="refreshOtpManually('icici'); return false;">
          <i class="fa fa-sync-alt"></i> Refresh
        </button>
      </div>
      <div id="icici-otp-container" class="d-flex flex-column gap-1 mb-3">
        <!-- OTPs will render here -->
        <div class="text-center text-muted small">Loading OTPs...</div>
      </div>

      <!-- TATA AIG OTP Section -->
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="mb-0 fw-semibold text-primary d-flex align-items-center gap-2">
          <i class="fa fa-shield"></i> TATA AIG OTPs
        </h6>
        <button class="btn btn-sm btn-outline-primary py-1" id="refresh-tataaig-btn" onclick="refreshOtpManually('tataaig'); return false;">
          <i class="fa fa-sync-alt"></i> Refresh
        </button>
      </div>
      <div id="tata-aig-container" class="d-flex flex-column gap-1">
        <!-- OTPs will render here -->
        <div class="text-center text-muted small">Loading OTPs...</div>
      </div>
    </div>
  </div>
</div>

<!-- Include the separate OTP manager JavaScript file -->
<script src="assets/js/otp-manager.js"></script>