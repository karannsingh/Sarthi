<?php
require '../vendor/autoload.php';
require '../include/config.php'; // Make sure this connects to your DB

use PhpOffice\PhpSpreadsheet\IOFactory;

function isValidMobile($mobile) {
    return preg_match('/^[6-9]\d{9}$/', $mobile);
}

function parseDate($date) {
    if (is_numeric($date)) {
        $timestamp = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($date);
    } else {
        $timestamp = strtotime($date);
    }
    return $timestamp ? date("d F Y", $timestamp) : false;
}

function isValidYear($year) {
    return preg_match('/^\d{4}$/', $year) && $year >= 1900 && $year <= (int)date("Y");
}

function vehicleExists($conn, $vehicleNumber) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM vehicle_details WHERE vehicle_number = ?");
    if (!$stmt) {
        return false; // Fail safe
    }
    $stmt->bind_param("s", $vehicleNumber);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    return $count > 0;
}

// Load file
if ($_FILES['excel_file']['error'] === 0) {
    $filePath = $_FILES['excel_file']['tmp_name'];
    $spreadsheet = IOFactory::load($filePath);
    $worksheet = $spreadsheet->getActiveSheet();
    $rows = $worksheet->toArray();

    echo "<h2>Preview Data (with Conversion, Validation & Duplicate Check)</h2>";
    echo "<form action='insert.php' method='POST'>";
    echo "<table border='1' style='border-collapse: collapse;'>";

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

    $validData = [];
    foreach ($rows as $index => $row) {
        if ($index == 0) continue;

        $rowData = [];
        $hasError = false;
        $rowHTML = "";

        for ($i = 0; $i < 13; $i++) {
            $value = trim($row[$i] ?? '');
            $error = '';

            // Validations
            if ($i == 3 && !isValidMobile($value)) {
                $error = "Invalid mobile number";
                $hasError = true;
            } elseif ($i == 4) {
                $parsed = parseDate($value);
                if (!$parsed) {
                    $error = "Invalid date";
                    $hasError = true;
                } else {
                    $value = $parsed;
                }
            } elseif (in_array($i, [5, 6, 7]) && !is_numeric($value)) {
                $error = "Must be numeric";
                $hasError = true;
            } elseif ($i == 12 && !isValidYear($value)) {
                $error = "Invalid year";
                $hasError = true;
            }

            // Vehicle Number Duplicate Check
            if ($i == 10 && vehicleExists($conn, $value)) {
                $error = "Duplicate vehicle";
                $hasError = true;
            }

            $rowHTML .= "<td title='$error'>" . htmlspecialchars($value) . "</td>";
            $rowData[] = $value;
        }

        // Mark whole row red if there's any error
        $rowStyle = $hasError ? "style='background-color: #f8d7da;'" : "";
        echo "<tr $rowStyle>$rowHTML</tr>";

        // Only add valid rows to data
        if (!$hasError) {
            $data[] = $rowData;
        }
    }

    echo "</table>";
    echo "<br><p><strong>Red rows are invalid or duplicates. Only clean data will be submitted.</strong></p>";
    echo "<input type='hidden' name='data' value='" . htmlspecialchars(json_encode($validData), ENT_QUOTES, 'UTF-8') . "'>";
    echo "<button type='submit'>Insert Valid Records</button>";
    echo "</form>";

} else {
    echo "File upload failed!";
}
?>