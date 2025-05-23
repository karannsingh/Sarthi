<?php
require '../vendor/autoload.php'; // Load PhpSpreadsheet
use PhpOffice\PhpSpreadsheet\IOFactory;

$host = "localhost:3306";
$dbname = "insurance";
$username = "root";
$password = "";

// Create MySQL Connection
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if (isset($_POST['upload'])) {
    if ($_FILES['excel_file']['error'] == 0) {
        $fileTmpPath = $_FILES['excel_file']['tmp_name'];
        $spreadsheet = IOFactory::load($fileTmpPath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        // Skip the first row (headers)
        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];

            // Extract data from Excel row
            $pyp_number = $conn->real_escape_string($row[0]);
            $customer_name = $conn->real_escape_string($row[1]);
            $email = $conn->real_escape_string($row[2]);
            $mobile = $conn->real_escape_string($row[3]);
            $renewal_date = date('Y-m-d', strtotime($row[4])); // Convert date format
            $idv = (int)$row[5];
            $premium = (int)$row[6];
            $ncb = (int)$row[7];
            $engine_number = $conn->real_escape_string($row[8]);
            $chassis_number = $conn->real_escape_string($row[9]);
            $vehicle_number = $conn->real_escape_string($row[10]);
            $vehicle_model = $conn->real_escape_string($row[11]);
            $mfy = (int)$row[12];

            // Insert into MySQL
            $query = "INSERT INTO vehicle_data (pyp_number, customer_name, email, mobile, renewal_date, idv, premium, ncb, engine_number, chassis_number, vehicle_number, vehicle_model, mfy) 
                      VALUES ('$pyp_number', '$customer_name', '$email', '$mobile', '$renewal_date', $idv, $premium, $ncb, '$engine_number', '$chassis_number', '$vehicle_number', '$vehicle_model', $mfy)";

            $conn->query($query);
        }

        echo "Data uploaded successfully!";
    } else {
        echo "Error uploading file!";
    }
}

$conn->close();
?>
