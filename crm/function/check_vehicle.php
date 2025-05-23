<?php
require('../include/config.php');
require('../include/functions.inc.php');

if (isset($_POST['vehicle_number'])) {
    $vehicle_number = trim($_POST['vehicle_number']);
    $query = "SELECT COUNT(*) as count FROM leads WHERE VehicleNumber = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $vehicle_number);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);

    echo ($data['count'] > 0) ? "exists" : "available";
}
?>