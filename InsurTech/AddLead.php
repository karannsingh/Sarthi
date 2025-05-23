<?php
require('include/top.php');
require('include/side-navbar.php');
require('include/right-side-navbar.php');

if(isset($_SESSION['USEROID'])){
  $statusQuery = "SELECT * FROM `master_remark_status` order by RemarkStatusOID ASC";
  $statusResult = mysqli_query($conn, $statusQuery);
}

?>
<div class="wrapper d-flex flex-column min-vh-100 bg-light bg-opacity-50 dark:bg-transparent">
  <?php
  require('include/header.php');
  ?>
  <div class="body flex-grow-1 px-3">
    <div class="container-lg">
      <div class="fs-2 fw-semibold">Add Lead</div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-4">
          <li class="breadcrumb-item">
            <!-- if breadcrumb is single--><span>Lead</span>
          </li>
          <li class="breadcrumb-item active"><span>Add Lead</span></li>
        </ol>
      </nav>
      <div class="row">
        <div class="col-lg-12 text-center">
          <div class="card mb-4">
            <div class="card-body p-4">
              <div class="card-title fs-4 fw-semibold">Lead Entry Form</div>
              <form id="leadForm" action="function/SaveLead.php" method="POST" enctype="multipart/form-data">
                <div class="row">
                  <div class="col-md-4 mb-4">
                    <div class="form-group">
                      <label>Customer Name</label>
                      <input type="text" name="customer_name" class="form-control" required>
                    </div>
                  </div>
                  <div class="col-md-4 mb-4">
                    <div class="form-group">
                      <label>Customer Number</label>
                      <input type="number" name="customer_number" id="customer_number" class="form-control" required maxlength="10">
                      <small id="number_warning" class="text-danger" style="display:none;">Enter a valid 10-digit number</small>
                    </div>
                  </div>

                  <div class="col-md-4 mb-4">
                    <div class="form-group">
                      <label>Vehicle Number</label>
                      <input type="text" name="vehicle_number" id="vehicle_number" class="form-control" required>
                      <small id="vehicle_warning" class="text-danger" style="display:none;">Vehicle number already exists!</small>
                    </div>
                  </div>
                  <div class="col-md-4 mb-4">
                    <div class="form-group">
                      <label>Registration Year</label>
                      <select name="reg_year" class="form-select" required>
                        <option value="">Select Year</option>
                        <?php
                        $currentYear = date("Y");
                        for ($year = $currentYear; $year >= 1981; $year--) {
                          echo "<option value='$year'>$year</option>";
                        }
                        ?>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-4 mb-4">
                    <div class="form-group">
                      <label>Renewal Date</label>
                      <input type="date" name="renewal_date" class="form-control" required>
                    </div>
                  </div>
                  <div class="col-md-4 mb-4">
                    <div class="form-group">
                      <label>Previous IDV</label>
                      <input type="number" name="previous_idv" step="0.01" class="form-control" required>
                    </div>
                  </div>
                  <div class="col-md-4 mb-4">
                    <div class="form-group">
                      <label>Previous Premium</label>
                      <input type="number" name="pre_premium" step="0.01" class="form-control" required>
                    </div>
                  </div>
                  <div class="col-md-4 mb-4">
                    <div class="form-group">
                      <label>Remark (Status)</label>
                      <select name="remark_status_id" class="form-select" required>
                        <option value="">Select Status</option>
                        <?php while ($row = mysqli_fetch_assoc($statusResult)) { ?>
                          <option value="<?= $row['RemarkStatusOID'] ?>"><?= $row['StatusName'] ?></option>
                        <?php } ?>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-4 mb-4">
                    <div class="form-group">
                      <label>Follow-Up Date</label>
                      <input type="date" name="follow_up_date" class="form-control" required>
                    </div>
                  </div>
                  <div class="col-md-4 mb-4">
                    <div class="form-group">
                      <label>Follow-Up Time</label>
                      <input type="time" name="follow_up_time" class="form-control" required>
                    </div>
                  </div>
                </div>
                <div class="row mb-4">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Link</label>
                      <textarea type="text" name="link" id="link" class="form-control" required></textarea>
                      <small id="link_warning" class="text-danger" style="display:none;">Enter a valid link.</small>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Feedback (Conversation)</label>
                      <textarea name="feedback" class="form-control" required></textarea>
                    </div>
                  </div>
                </div>
                <div class="row mb-4">
                  <div class="col-md-6">
                    <label for="documents">Upload Documents:</label>
                    <input type="file" name="documents[]" id="documents" class="form-control" multiple>
                  </div>
                  <div class="col-md-6">
                  </div>
                </div>
                <button type="submit" id="saveLeadBtn" class="btn btn-primary text-white">Save Lead</button>
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
  <script type="text/javascript">
    $(document).ready(function() {
      function checkValidation() {
        if ($("#number_warning").is(":visible") || $("#vehicle_warning").is(":visible") || $("#link_warning").is(":visible")) {
          $("#saveLeadBtn").prop("disabled", true);
        } else {
          $("#saveLeadBtn").prop("disabled", false);
        }
      }

    // Validate Customer Number in real-time
      $("#customer_number").on("input", function() {
        var number = $(this).val();
        if (/^\d{10}$/.test(number)) {
          $("#number_warning").hide();
        } else {
          $("#number_warning").show();
        }
        checkValidation();
      });

    // Check Vehicle Number in the database in real-time
      $("#vehicle_number").on("input", function() {
        var vehicle_number = $(this).val();
        if (vehicle_number.length > 3) { // Start checking after 3 characters
          $.ajax({
            url: "function/check_vehicle.php",
            method: "POST",
            data: { vehicle_number: vehicle_number },
            success: function(response) {
              if (response.trim() == "exists") {
                $("#vehicle_warning").show();
              } else {
                $("#vehicle_warning").hide();
              }
              checkValidation();
            }
          });
        } else {
          $("#vehicle_warning").hide();
          checkValidation();
        }
      });

      /*$("#link").on("input", function() {
        var link = $(this).val();
        var validPatterns = [
            "https://pbpci.policybazaar.com/v2/quotes?",
            "https://pbpci.policybazaar.com/v2/proposal?",
            "https://cvpbp.policybazaar.com/",
            "https://pbptwowheeler.policybazaar.com/"
        ];

        var isValid = validPatterns.some(pattern => link.startsWith(pattern));

        if (isValid) {
            $("#link_warning").hide();
        } else {
            $("#link_warning").show();
        }
        
        checkValidation();
      });*/
    });
  </script>