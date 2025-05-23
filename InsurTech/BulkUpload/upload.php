<?php
require '../include/config.php';
require '../vendor/autoload.php';

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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bulk Vehicle Upload</title>
</head>
<body>
<h2>Upload Vehicle Data (Excel)</h2>

<form action="" method="POST" enctype="multipart/form-data" id="uploadForm">
    <input type="file" name="excel_file" accept=".xls,.xlsx" required>
    <button type="submit" name="preview">📤 Upload & Preview</button>
    <button type="submit" name="submit_data" id="insertBtn" disabled>✅ Insert Valid Records</button>
    <input type="hidden" name="valid_data" id="validDataInput">
</form>
<hr>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['preview']) && isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] === 0) {
    $conn = new mysqli($server, $user, $pass, $database);
    $filePath = $_FILES['excel_file']['tmp_name'];
    $spreadsheet = IOFactory::load($filePath);
    $worksheet = $spreadsheet->getActiveSheet();
    $rows = $worksheet->toArray();

    $validData = [];
    $hasAnyError = false;

    echo "<h3>Preview</h3>";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr>
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
        </tr>";

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

        $rowStyle = $hasError ? "style='background-color: #f8d7da;'" : "";
        echo "<tr $rowStyle>$rowHTML</tr>";

        if (!$hasError) {
            $validData[] = $rowData;
        } else {
            $hasAnyError = true;
        }
    }
    echo "</table>";

    // Safely inject JSON into JavaScript
    echo "<script>
        const validData = " . json_encode($validData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) . ";
        document.getElementById('validDataInput').value = JSON.stringify(validData);
    </script>";

    if (!$hasAnyError && count($validData) > 0) {
        echo "<script>document.getElementById('insertBtn').disabled = false;</script>";
    }
}
?>
</body>
<script>
    document.getElementById('insertBtn').addEventListener('click', function () {
        document.querySelector('input[name="excel_file"]').removeAttribute('required');
    });
</script>
</html>