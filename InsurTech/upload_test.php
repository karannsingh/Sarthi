<?php
require("include/top.php");
require("include/side-navbar.php");
require("include/right-side-navbar.php");

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

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
    $stmt = $conn->prepare("SELECT COUNT(*) FROM vehicle_data WHERE vehicle_number = ?");
    $stmt->bind_param("s", $vehicleNumber);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    return $count > 0;
}

// Handle Insert
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit_data'])) {
    $conn = new mysqli($server, $user, $pass, $database);
    $data = isset($_POST['valid_data']) ? json_decode($_POST['valid_data'], true) : null;
    $inserted = 0;

    if (is_array($data)) {
        foreach ($data as $row) {
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

        echo "<div style='padding: 10px; background-color: #d4edda;'>✅ Inserted $inserted records successfully.</div><br>";
    } else {
        echo "<div style='color:red;'>❌ No valid data received or JSON parsing failed.</div>";
    }
}
?>
<style type="text/css">
    #previewTable {
        white-space: nowrap;
    }
</style>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- THEN DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<!-- THEN DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<div class="wrapper d-flex flex-column min-vh-100 bg-light bg-opacity-50 dark:bg-transparent">
  <?php
  require('include/header.php');
  ?>
  <div class="body flex-grow-1 px-3">
    <div class="container-lg">
      <div class="fs-2 fw-semibold">Bulk Upload</div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-4">
          <li class="breadcrumb-item">
            <!-- if breadcrumb is single--><span>Bulk Upload</span>
          </li>
          <li class="breadcrumb-item active"><span>Upload Vehicle</span></li>
        </ol>
      </nav>
      <div class="row">
  <div class="col-lg-12">
    <div class="card mb-4">
      <div class="card-body">
        <h4 class="card-title mb-4 fw-semibold text-center">Upload Vehicle Data (Excel)</h4>

        <!-- Upload Form -->
        <form action="" method="POST" enctype="multipart/form-data" id="uploadForm">
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

        <!-- Data Table Preview -->
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

              for ($i = 0; $i < 13; $i++) {
                $value = trim($row[$i] ?? '');
                $error = "";

                if (!empty($value)) {
                  if ($i == 3 && !isValidMobile($value)) {
                    $error = "❌ Invalid Mobile";
                    $hasError = true;
                  } elseif ($i == 4) {
                    $parsed = parseDate($value);
                    if (!$parsed) {
                      $error = "❌ Invalid Date";
                      $hasError = true;
                    } else {
                      $value = $parsed;
                    }
                  } elseif (in_array($i, [5, 6, 7]) && !is_numeric($value)) {
                    $error = "❌ Not Numeric";
                    $hasError = true;
                  } elseif ($i == 12 && !isValidYear($value)) {
                    $error = "❌ Invalid Year";
                    $hasError = true;
                  } elseif ($i == 10 && vehicleExists($conn, $value)) {
                    $error = "❌ Duplicate Vehicle";
                    $hasError = true;
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

            // Safely inject valid data
            echo "<script>
              const validData = " . json_encode($validData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) . ";
              document.getElementById('validDataInput').value = JSON.stringify(validData);
            </script>";

            if (!$hasAnyError && count($validData) > 0) {
              echo "<script>document.getElementById('insertBtn').disabled = false;</script>";
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
  
<?php
    require('include/footer.php');
?>
<script>
  document.getElementById('insertBtn').addEventListener('click', function () {
      document.querySelector('input[name="excel_file"]').removeAttribute('required');
  });

  $(document).ready(function () {
    $('#previewTable').DataTable({
      responsive: true,
      paging: true,
      pagingType: 'full_numbers',
      pageLength: 25,
      ordering: true,
      order: [[0, 'asc']],
      lengthMenu: [5, 10, 25, 50, 100],
    });
  });
</script>