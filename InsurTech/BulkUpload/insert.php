<?php
require '../include/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['data'])) {
    $data = json_decode($_POST['data'], true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        die("Error: Invalid JSON format! " . json_last_error_msg());
    }

    $conn = new mysqli($server, $user, $pass, $database);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $inserted = 0;
    foreach ($data as $row) {
        $stmt = $conn->prepare("INSERT INTO vehicle_data 
            (policy_number, customer_name, email, mobile_number, renewal_date, idv, premium, ncb, engine_number, chassis_number, vehicle_number, vehicle_model, manufacturing_year) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param("sssssssssssss", ...$row);

        if ($stmt->execute()) {
            $inserted++;
        } else {
            echo "Insert error for vehicle number {$row[10]}: " . $stmt->error . "<br>";
        }
    }

    echo "<div style='padding:20px; font-family:sans-serif;'>
            <h2>$inserted Record(s) Inserted Successfully.</h2>
            <a href='upload.php'>← Go Back to Upload</a>
          </div>";

    $conn->close();
} else {
    echo "Invalid request!";
}
?>