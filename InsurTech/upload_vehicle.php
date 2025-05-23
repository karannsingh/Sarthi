<?php
require("include/top.php");

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

function isValidMobile($mobile) {
    return preg_match('/^[6-9]\d{9}$/', $mobile);
}

function parseDate($date) {
    $timestamp = strtotime($date);
    return $timestamp ? date("Y-m-d", $timestamp) : false;
}

function isValidYear($year) {
    return preg_match('/^\d{4}$/', $year) && $year >= 1900 && $year <= (int)date("Y");
}

function vehicleExists($conn, $vehicleNumber) {
    $stmt = $conn->prepare("SELECT renewal_date FROM vehicle_data WHERE vehicle_number = ?");
    $stmt->bind_param("s", $vehicleNumber);
    $stmt->execute();
    $stmt->bind_result($existingRenewalDate);
    if ($stmt->fetch()) {
        $stmt->close();
        return $existingRenewalDate;  // return renewal date if vehicle exists
    }
    $stmt->close();
    return false; // not found
}

function getVehicleRenewalDate($conn, $vehicleNumber) {
    $stmt = $conn->prepare("SELECT renewal_date FROM vehicle_data WHERE vehicle_number = ?");
    $stmt->bind_param("s", $vehicleNumber);
    $stmt->execute();
    $stmt->bind_result($existingRenewal);
    if ($stmt->fetch()) {
        return $existingRenewal;
    }
    return false;
}

// Default to empty (no selection)
$activeSection = '';

// If there's a form submission, try to determine which section should be active
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the form contains a hidden field indicating the section
    if (isset($_POST['data_type'])) {
        $activeSection = $_POST['data_type'];
    } else {
        // If no data_type is specified but we have preview or submit_data, default to vehicle
        // This is for backward compatibility
        if (isset($_POST['preview']) || isset($_POST['submit_data'])) {
            $activeSection = 'vehicle';
        }
    }
}

$successMessage = '';
$errorMessage = '';

// Handle Insert/Update
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit_data'])) {
    $conn = new mysqli($server, $user, $pass, $database);
    $data = isset($_POST['valid_data']) ? json_decode($_POST['valid_data'], true) : null;
    $inserted = 0;
    $updated = 0;
    $skipped = 0;

    if (is_array($data)) {
        foreach ($data as $row) {
            $vehicleNumber = $row[10];
            $newRenewalDate = $row[4];
            $existingRenewalDate = getVehicleRenewalDate($conn, $vehicleNumber);

            if ($existingRenewalDate !== false) {
                if ($existingRenewalDate === $newRenewalDate) {
                    $skipped++;
                    continue;
                } else {
                    // Update existing record
                    $stmt = $conn->prepare("UPDATE vehicle_data SET 
                        policy_number = ?, customer_name = ?, email = ?, mobile_number = ?, renewal_date = ?, idv = ?, premium = ?, ncb = ?, engine_number = ?, chassis_number = ?, vehicle_model = ?, manufacturing_year = ? 
                        WHERE vehicle_number = ?");
                    
                    $stmt->bind_param("sssssssssssss",
                        $row[0], $row[1], $row[2], $row[3], $row[4],
                        $row[5], $row[6], $row[7], $row[8], $row[9],
                        $row[11], $row[12], $vehicleNumber);

                    if ($stmt->execute()) {
                        $updated++;
                    }
                }
            } else {
                // Insert new record
                $stmt = $conn->prepare("INSERT INTO vehicle_data 
                    (policy_number, customer_name, email, mobile_number, renewal_date, idv, premium, ncb, engine_number, chassis_number, vehicle_number, vehicle_model, manufacturing_year) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                $stmt->bind_param("sssssssssssss",
                    $row[0], $row[1], $row[2], $row[3], $row[4],
                    $row[5], $row[6], $row[7], $row[8], $row[9],
                    $row[10], $row[11], $row[12]);

                if ($stmt->execute()) {
                    $inserted++;
                }
            }
        }

        $successMessage = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                            ✅ Inserted: $inserted | Updated: $updated | Skipped (duplicate): $skipped
                            <button type='button' class='btn-close' data-coreui-dismiss='alert' aria-label='Close'></button>
                          </div>";
    } else {
        $errorMessage = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                          ❌ No valid data received or JSON parsing failed.
                          <button type='button' class='btn-close' data-coreui-dismiss='alert' aria-label='Close'></button>
                        </div>";
    }
}

require("include/side-navbar.php");
require("include/right-side-navbar.php");
?>
<style>
    #previewTable {
        white-space: nowrap;
    }
    .upload-section {
        display: none;
    }
    .upload-section.active {
        display: block;
    }
    .template-download {
        margin-bottom: 15px;
    }
</style>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<div class="wrapper d-flex flex-column min-vh-100 bg-light bg-opacity-50 dark:bg-transparent">
<?php require('include/header.php'); ?>
<div class="body flex-grow-1 px-3">
  <div class="container-lg">
    <div class="fs-2 fw-semibold">Bulk Upload</div>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><span>Bulk Upload</span></li>
        <li class="breadcrumb-item active"><span>Upload Data</span></li>
      </ol>
    </nav>
    <!-- Display success or error messages right after breadcrumb -->
    <?php 
    if (!empty($successMessage)) {
        echo $successMessage;
    }
    if (!empty($errorMessage)) {
        echo $errorMessage;
    }
    ?>
    <div class="row">
      <div class="col-lg-12">
        <div class="card mb-4">
          <div class="card-body">
            <h4 class="card-title mb-4 fw-semibold text-center">Select Data Type to Upload</h4>
            
            <!-- Data Type Selection Dropdown -->
            <div class="row g-3 d-flex justify-content-center mb-4">
              <div class="col-md-5">
                <select class="form-select" id="dataTypeSelect">
                  <option value="">-- Select Data Type --</option>
                  <option value="vehicle" <?php echo ($activeSection == 'vehicle') ? 'selected' : ''; ?>>Vehicle Data</option>
                  <option value="health" disabled>Health Data</option>
                  <option value="personal" disabled>Personal Loan Data</option>
                  <option value="credit" disabled>Credit Card Data</option>
                </select>
              </div>
            </div>

            <!-- Vehicle Data Section -->
            <div id="vehicleDataSection" class="upload-section <?php echo ($activeSection == 'vehicle') ? 'active' : ''; ?>">
              <h5 class="card-subtitle mb-3 fw-semibold text-center">Upload Vehicle Data (Excel)</h5>
              
              <!-- Template Download Button -->
              <div class="row template-download d-flex justify-content-center">
                <div class="col-md-5 text-center">
                  <a href="download_template.php" class="btn btn-outline-primary">
                    <i class="fa fa-download"></i> Download Template
                  </a>
                </div>
              </div>

              <form action="" method="POST" enctype="multipart/form-data" id="uploadForm">
                <input type="hidden" name="data_type" id="hiddenDataType" value="vehicle">
                <div class="row g-3 d-flex justify-content-center">
                  <div class="col-md-5">
                    <input type="file" class="form-control" name="excel_file" accept=".xls,.xlsx" required>
                  </div>
                  <div class="col-md-12 d-flex justify-content-center gap-3">
                    <button type="submit" class="btn btn-primary" name="preview">📤 Upload & Preview</button>
                    <button type="submit" class="btn btn-success" name="submit_data" id="insertBtn" disabled>✅ Insert Valid Records</button>
                    <input type="hidden" name="valid_data" id="validDataInput">
                  </div>
                </div>
              </form>
            </div>

            <!-- Health Data Section (Disabled) -->
            <div id="healthDataSection" class="upload-section <?php echo ($activeSection == 'health') ? 'active' : ''; ?>">
              <div class="alert alert-info alert-dismissible fade show" role="alert">
                Health Data upload functionality is coming soon.
                <button type="button" class="btn-close" data-coreui-dismiss="alert" aria-label="Close"></button>
              </div>
            </div>

            <!-- Personal Loan Data Section (Disabled) -->
            <div id="personalLoanDataSection" class="upload-section <?php echo ($activeSection == 'personal') ? 'active' : ''; ?>">
              <div class="alert alert-info alert-dismissible fade show" role="alert">
                Personal Loan Data upload functionality is coming soon.
                <button type="button" class="btn-close" data-coreui-dismiss="alert" aria-label="Close"></button>
              </div>
            </div>

            <!-- Credit Card Data Section (Disabled) -->
            <div id="creditCardDataSection" class="upload-section <?php echo ($activeSection == 'credit') ? 'active' : ''; ?>">
              <div class="alert alert-info alert-dismissible fade show" role="alert">
                Credit Card Data upload functionality is coming soon.
                <button type="button" class="btn-close" data-coreui-dismiss="alert" aria-label="Close"></button>
              </div>
            </div>

            <!-- Data Preview -->
            <div class="table-responsive mt-4">
              <?php
              if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['preview']) && isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] === 0) {
                $conn = new mysqli($server, $user, $pass, $database);
                $filePath = $_FILES['excel_file']['tmp_name'];
                $spreadsheet = IOFactory::load($filePath);
                $worksheet = $spreadsheet->getActiveSheet();
                $rows = $worksheet->toArray();

                $validData = [];
                $hasAnyError = false;
                $firstRow = $rows[0] ?? [];
                
                // Expected headers for vehicle data
                $expectedHeaders = [
                  'Policy Number', 'Customer Name', 'Email', 'Mobile Number', 
                  'Renewal Date', 'IDV', 'Premium', 'NCB', 
                  'Engine Number', 'Chassis Number', 'Vehicle Number', 
                  'Vehicle Model', 'Manufacturing Year'
                ];
                
                // Check if headers match expected format
                $headersMatch = true;
                foreach ($expectedHeaders as $index => $header) {
                  if (!isset($firstRow[$index]) || strtolower(trim($firstRow[$index])) !== strtolower(trim($header))) {
                    $headersMatch = false;
                    break;
                  }
                }
                
                if (!$headersMatch) {
                  echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                    <strong>Error:</strong> The uploaded file does not match the expected template format. 
                    Please download and use the correct template.
                    <button type='button' class='btn-close' data-coreui-dismiss='alert' aria-label='Close'></button>
                  </div>";
                  echo "<script>document.getElementById('insertBtn').disabled = true;</script>";
                } else {
                  echo "<table class='table table-striped table-bordered align-middle' id='previewTable'>";
                  echo "<thead class='table-light'><tr>
                          <th>Policy Number</th>
                          <th>Customer Name</th>
                          <th>Email</th>
                          <th>Mobile Number</th>
                          <th>Renewal Date</th>
                          <th>IDV</th>
                          <th>Premium</th>
                          <th>NCB</th>
                          <th>Engine Number</th>
                          <th>Chassis Number</th>
                          <th>Vehicle Number</th>
                          <th>Vehicle Model</th>
                          <th>Manufacturing Year</th>
                        </tr></thead><tbody>";

                  foreach ($rows as $index => $row) {
                    if ($index == 0) continue;

                    $hasError = false;
                    $rowData = [];
                    $rowHTML = "";

                    $vehicleNumber = trim($row[10] ?? '');
                    $renewalRaw = trim($row[4] ?? '');
                    $parsedRenewal = parseDate($renewalRaw);

                    for ($i = 0; $i < 13; $i++) {
                      $value = trim($row[$i] ?? '');
                      $error = "";

                      if (!empty($value)) {
                        if ($i == 3 && !isValidMobile($value)) {
                          $error = "❌ Invalid Mobile";
                          $hasError = true;
                        } elseif ($i == 4) {
                            if (!$parsedRenewal) {
                                $error = "❌ Invalid Date";
                                $hasError = true;
                            } else {
                                $value = $parsedRenewal;
                            }
                        } elseif (in_array($i, [5, 6, 7]) && !is_numeric($value)) {
                          $error = "❌ Not Numeric";
                          $hasError = true;
                        } elseif ($i == 12 && !isValidYear($value)) {
                          $error = "❌ Invalid Year";
                          $hasError = true;
                        }
                        elseif ($i == 10) {
                            $existingRenewal = vehicleExists($conn, $value);
                            if ($existingRenewal !== false) {
                                if ($parsedRenewal === $existingRenewal) {
                                    $error = "❌ Duplicate Vehicle & Same Renewal";
                                    $hasError = true;
                                }
                                // if renewal date is different → do nothing (allow update)
                            }
                        }
                      }

                      $rowData[] = $value;
                      $tooltip = $error ? "$value ($error)" : $value;
                      $rowHTML .= "<td title='" . htmlspecialchars($tooltip, ENT_QUOTES) . "'>" . htmlspecialchars($value) . "</td>";
                    }

                    $rowClass = $hasError ? "table-danger" : "";
                    echo "<tr class='$rowClass'>$rowHTML</tr>";

                    if (!$hasError) {
                      $validData[] = $rowData;
                    } else {
                      $hasAnyError = true;
                    }
                  }

                  echo "</tbody></table>";

                  echo "<script>
                    const validData = " . json_encode($validData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) . ";
                    document.getElementById('validDataInput').value = JSON.stringify(validData);
                  </script>";

                  if (!$hasAnyError && count($validData) > 0) {
                    echo "<script>document.getElementById('insertBtn').disabled = false;</script>";
                  }
                }
              }
              ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php require('include/footer.php'); ?>
<script>
  document.getElementById('insertBtn').addEventListener('click', function () {
    document.querySelector('input[name="excel_file"]').removeAttribute('required');
  });

  $(document).ready(function () {
    // Initialize DataTable if it exists
    if ($('#previewTable').length) {
      $('#previewTable').DataTable({
        responsive: true,
        paging: true,
        pagingType: 'full_numbers',
        pageLength: 25,
        ordering: true,
        order: [[0, 'asc']],
        lengthMenu: [5, 10, 25, 50, 100],
      });
      
      // Since we're showing the table, make sure the vehicle section is visible
      // This is a safeguard in case the PHP code didn't set it correctly
      $('#vehicleDataSection').addClass('active');
      $('#dataTypeSelect').val('vehicle');
      $('#hiddenDataType').val('vehicle');
    }
    
    // Data type selection dropdown handler
    $('#dataTypeSelect').on('change', function() {
      const selectedValue = $(this).val();
      // Update the hidden field value
      $('#hiddenDataType').val(selectedValue);
      
      // Hide all sections first
      $('.upload-section').removeClass('active');
      
      // Show the selected section
      if (selectedValue === 'vehicle') {
        $('#vehicleDataSection').addClass('active');
      } else if (selectedValue === 'health') {
        $('#healthDataSection').addClass('active');
      } else if (selectedValue === 'personal') {
        $('#personalLoanDataSection').addClass('active');
      } else if (selectedValue === 'credit') {
        $('#creditCardDataSection').addClass('active');
      }
    });
  });
</script>