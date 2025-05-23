<?php
// otp/otp_view_log.php
header('Content-Type: application/json');
require('../include/config.php');
require('../include/auth_check.php'); // Ensure user is logged in
date_default_timezone_set('Asia/Kolkata');

// Get data from request
$employeeId = $_SESSION['USEROID'] ?? null;
$otpId = $_POST['otp_id'] ?? null;
$companyId = $_POST['company_id'] ?? null;

// Validate required fields
if (!$employeeId || !$otpId || !$companyId) {
    http_response_code(400);
    echo json_encode([
        "status" => "error", 
        "message" => "Missing required fields: employee ID, OTP ID, or company ID"
    ]);
    exit;
}

try {
    // Convert parameters to integers to ensure type safety
    $otpId = (int)$otpId;
    $companyId = (int)$companyId;
    $employeeId = (int)$employeeId;
    
    // Log the OTP access - always log access when requested
    // This ensures OTPs are properly marked as viewed
    $stmt = $conn->prepare("INSERT INTO otp_view_log 
                          (otp_id, company_id, employee_id, access_time) 
                          VALUES (?, ?, ?, NOW())
                          ON DUPLICATE KEY UPDATE access_time = NOW()");
    $stmt->bind_param("iii", $otpId, $companyId, $employeeId);
    
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "OTP access logged successfully"]);
    } else {
        throw new Exception("Failed to log OTP access: " . $stmt->error);
    }
    
    $stmt->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
