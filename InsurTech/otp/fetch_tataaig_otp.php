<?php
// otp/fetch_tataaig_otp.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require('../include/config.php');
require('../include/auth_check.php'); // Ensure user is logged in
date_default_timezone_set('Asia/Kolkata');

header('Content-Type: application/json');

try {
    // Log the fetch request with employee ID
    $employeeId = $_SESSION['USEROID'] ?? null;
    /*if ($employeeId) {
        $logStmt = $conn->prepare("INSERT INTO otp_fetch_log (employee_id, company_id, fetch_time) VALUES (?, 3, NOW())");
        $logStmt->bind_param("i", $employeeId);
        $logStmt->execute();
        $logStmt->close();
    }*/

    // Email settings
    $hostname = '{imap.gmail.com:993/imap/ssl/novalidate-cert}INBOX';
    $username = 'tataaigworks@gmail.com';
    $password = 'bggx xozz yzso efge';

    // Set connection options
    $options = [
        'DISABLE_AUTHENTICATOR' => 'PLAIN'
    ];

    // Function to safely connect to IMAP
    function safeImapConnect($hostname, $username, $password, $options = []) {
        $attempts = 0;
        $maxAttempts = 2;
        
        while ($attempts < $maxAttempts) {
            $inbox = @imap_open($hostname, $username, $password, 0, 1, $options);
            if ($inbox) {
                return $inbox;
            }
            $attempts++;
            sleep(1); // Wait 1 second before retrying
        }
        
        return false;
    }

    // Safely connect to email
    $inbox = safeImapConnect($hostname, $username, $password, $options);
    if (!$inbox) {
        throw new Exception("Failed to connect to email: " . imap_last_error());
    }

    // Get current date in format that IMAP understands
    $today = date("d-M-Y");

    // Search for OTP emails from today
    $searchQuery = 'FROM "No-reply@tataaig.com" SUBJECT "One Time Password (OTP) for Policy issuance from TATA AIG" SINCE "' . $today . '"';
    $emails = imap_search($inbox, $searchQuery);

    $response = [];
    if ($emails && count($emails) > 0) {
        rsort($emails); // Latest first
        $latest_email = $emails[0];
        
        // Get email headers for metadata
        $headers = imap_headerinfo($inbox, $latest_email);
        $email_date = date('Y-m-d H:i:s', strtotime($headers->date));
        
        // Get email body
        $message = imap_fetchbody($inbox, $latest_email, 1);
        
        // Extract OTP from message
        if (preg_match('/\b\d{6}\b/', $message, $matches)) {
            $otp = $matches[0];
            $today = date('Y-m-d');
            
            // Check if OTP already exists in database
            $stmt = $conn->prepare("SELECT id FROM tataaig_otp_log WHERE otp = ? AND DATE(created_time) = ?");
            $stmt->bind_param("ss", $otp, $today);
            $stmt->execute();
            $stmt->store_result();
            
            if ($stmt->num_rows === 0) {
                $stmt->close();
                
                // Insert new OTP
                $insertStmt = $conn->prepare("INSERT INTO tataaig_otp_log (otp, created_time) VALUES (?, NOW())");
                $insertStmt->bind_param("s", $otp);
                $insertStmt->execute();
                $insertStmt->close();
                
                // Mark email as seen
                imap_setflag_full($inbox, $latest_email, "\\Seen");
                
                $response = [
                    "status" => "success", 
                    "otp" => $otp,
                    "message" => "New OTP fetched successfully"
                ];
            } else {
                $stmt->close();
                // Mark email as seen since we've already processed it
                imap_setflag_full($inbox, $latest_email, "\\Seen");
                
                $response = [
                    "status" => "success", 
                    "otp" => $otp,
                    "message" => "OTP already exists."
                ];
            }
        } else {
            $response = [
                "status" => "success", 
                "message" => "OTP pattern not found in email"
            ];
        }
    } else {
        $response = [
            "status" => "success", 
            "message" => "No new OTP emails found today"
        ];
    }

    // Close mailbox
    imap_close($inbox);

    // Send response
    echo json_encode($response);
} catch (Exception $e) {
    // Close mailbox if open
    if (isset($inbox) && $inbox) {
        imap_close($inbox);
    }
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?>
