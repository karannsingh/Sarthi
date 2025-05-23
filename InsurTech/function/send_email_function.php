<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../vendor/autoload.php'; // Adjust path as needed
//require_once '../include/config.php'; // Your DB connection file

function send_custom_email($to_email, $subject, $body, $signature_type = 1) {
    global $conn;

    // Static signatures
    $signatures = [
        1 => "<br><br>Best Regards,<br>Sarthi Enterprises<br><a href='https://sarthii.co.in'>Visit our website</a>",
        2 => "<br><br>Thanks & Regards,<br>Team Support<br>Contact: 1234567890",
        3 => "<br><br>Warm Regards,<br>Sales Team<br>Your Company",
    ];

    // Add selected signature
    $signature = isset($signatures[$signature_type]) ? $signatures[$signature_type] : $signatures[1];
    $full_body = $body . $signature; // Full email content

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.hostinger.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'sarthi@sarthii.co.in';
        $mail->Password = 'K?69rn6j1I2c';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('sarthi@sarthii.co.in', 'Sarthi Enterprises');
        $mail->addAddress($to_email);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = nl2br($full_body);

        if ($mail->send()) {
            // Insert Success Log
            $stmt = $conn->prepare("INSERT INTO email_logs (to_email, subject, body, status) VALUES (?, ?, ?, 'Success')");
            $stmt->bind_param("sss", $to_email, $subject, $full_body);
            $stmt->execute();
            $stmt->close();

            return true;
        } else {
            // Insert Failed Log
            $stmt = $conn->prepare("INSERT INTO email_logs (to_email, subject, body, status, error_message) VALUES (?, ?, ?, 'Failed', ?)");
            $error = $mail->ErrorInfo;
            $stmt->bind_param("ssss", $to_email, $subject, $full_body, $error);
            $stmt->execute();
            $stmt->close();

            return false;
        }
    } catch (Exception $e) {
        // Insert Exception Log
        $stmt = $conn->prepare("INSERT INTO email_logs (to_email, subject, body, status, error_message) VALUES (?, ?, ?, 'Failed', ?)");
        $error = $e->getMessage();
        $stmt->bind_param("ssss", $to_email, $subject, $full_body, $error);
        $stmt->execute();
        $stmt->close();

        error_log("Email sending error: " . $e->getMessage());
        return false;
    }
}
?>