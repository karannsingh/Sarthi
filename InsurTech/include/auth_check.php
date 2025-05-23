<?php
// include/auth_check.php (Simple auth check to include in OTP scripts)
if (!isset($_SESSION['USEROID']) || empty($_SESSION['USEROID'])) {
    header('Content-Type: application/json');
    echo json_encode([
        "status" => "error",
        "message" => "Authentication required"
    ]);
    exit;
}
?>