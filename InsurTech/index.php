<!-- index.php -->
<?php
require('include/top.php');
require('include/side-navbar.php');
require('include/right-side-navbar.php');
?>
<style>
  .copy-btn {
    cursor: pointer;
    color: #007bff;
    margin-left: 8px;
  }
  .copy-btn:hover {
    color: #0056b3;
  }
  .card-title img {
    max-height: 40px;
    object-fit: contain;
  }
  .card-title {
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  .redirect-icon {
    color: #0d6efd;
    font-size: 1.1rem;
    text-decoration: none;
  }
</style>
    <div class="wrapper d-flex flex-column min-vh-100 bg-light bg-opacity-50 dark:bg-transparent">
      <?php
        require('include/header.php');
      ?>
      <div class="body flex-grow-1 px-3">
        <div class="container-lg">
          <div class="fs-2 fw-semibold">Dashboard</div>
          <!-- <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-4">
              <li class="breadcrumb-item">
                <span>Home</span>
              </li>
              <li class="breadcrumb-item active"><span>Dashboard</span></li>
            </ol>
          </nav> -->
          <div class="my-2">
            <div class="row">
              <!-- Card 1 -->
              <div class="col-md-4 mb-4">
                <div class="card shadow-sm rounded-3 h-100">
                  <div class="card-body">
                    <h5 class="card-title">
                      <img src="assets/img/company/pbp-logo.png" alt="PBP Logo">
                      <a href="https://www.pbpartners.com/v1/partner-dashboard" target="_blank" class="redirect-icon">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                      </a>
                    </h5>
                    <p class="card-text mb-2">
                      Username: <span id="user1">8928673855</span>
                      <i class="fa fa-copy copy-btn" onclick="copyToClipboard('user1')"></i><br>
                      OTP Based LOGIN
                    </p>
                  </div>
                </div>
              </div>

              <!-- Card 2 -->
              <div class="col-md-4 mb-4">
                <div class="card shadow-sm rounded-3 h-100">
                  <div class="card-body">
                    <h5 class="card-title">
                      <img src="assets/img/company/icici.PNG" alt="ICICI Logo">
                      <a href="https://nysa.icicilombard.com/#/login" target="_blank" class="redirect-icon">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                      </a>
                    </h5>
                    <p class="card-text mb-2">
                      Username: <span id="user2">IM-2237004</span>
                      <i class="fa fa-copy copy-btn" onclick="copyToClipboard('user2')"></i><br>
                      Password: <span id="pass2" data-full="sarthi@123">s*****@**3</span>
                      <i class="fa fa-copy copy-btn" onclick="copyToClipboard('pass2')"></i><br>
                      OTP Based LOGIN
                    </p>
                  </div>
                </div>
              </div>

              <!-- Card 3 -->
              <div class="col-md-4 mb-4">
                <div class="card shadow-sm rounded-3 h-100">
                  <div class="card-body">
                    <h5 class="card-title">
                      <img src="assets/img/company/tata.png" alt="TATA AIG Logo">
                      <a href="https://sellonline.tataaig.com/ipdsv2/login/#/login" target="_blank" class="redirect-icon">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                      </a>
                    </h5>
                    <p class="card-text mb-2">
                      Username: <span id="user3">tataaigworks@gmail.com</span>
                      <i class="fa fa-copy copy-btn" onclick="copyToClipboard('user3')"></i><br>
                      OTP Based LOGIN
                    </p>
                  </div>
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
  function copyToClipboard(elementId) {
    const span = document.getElementById(elementId);
    const text = span.dataset.full || span.textContent;
    navigator.clipboard.writeText(text).then(() => {
      alert('Credential Copied!');
    });
  }
</script>