<?php
// otp/get_pb_otps.php
header('Content-Type: application/json');
require('../include/config.php');
require('../include/functions.inc.php');
require('../include/auth_check.php'); // Ensure user is logged in
date_default_timezone_set('Asia/Kolkata');

// Error handling
try {
    // Get OTPs with viewed status
    /*$sql = "SELECT l.id, l.otp, l.created_time, 
                  CASE WHEN v.otp_id IS NOT NULL THEN 1 ELSE 0 END AS viewed
            FROM pb_otp_log l
            LEFT JOIN otp_view_log v ON l.id = v.otp_id AND v.company_id = 1
            WHERE l.otp IS NOT NULL AND l.otp != ''
            ORDER BY l.created_time DESC LIMIT 10";*/

    $sql = "SELECT l.id, l.otp, l.created_time, 
                  CASE WHEN v.otp_id IS NOT NULL THEN 1 ELSE 0 END AS viewed,
                  MAX(v.access_time) as viewed_time,
                  u.UserName AS viewed_by
            FROM pb_otp_log l
            LEFT JOIN otp_view_log v ON l.id = v.otp_id AND v.company_id = 1
            LEFT JOIN users u ON v.employee_id = u.UserOID
            WHERE l.otp IS NOT NULL AND l.otp != ''
            GROUP BY l.id
            ORDER BY l.created_time DESC LIMIT 10";

    $result = $conn->query($sql);
    if (!$result) {
        throw new Exception($conn->error);
    }

    $otps = [];
    while ($row = $result->fetch_assoc()) {
        $otps[] = $row;
    }

    echo json_encode([
        "status" => "success",
        "data" => $otps
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Database error: " . $e->getMessage()
    ]);
}
?>
