<?php
// Using autoload for better dependency management
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once '../vendor/autoload.php';
require_once '../include/config.php'; // For database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $customer_email = $_POST['customer_email'];
    $customer_name = $_POST['customer_name'];
    $vehicle_number = isset($_POST['vehicle_number']) ? $_POST['vehicle_number'] : '';
    $total_premium = isset($_POST['total_premium']) ? $_POST['total_premium'] : '';
    $link = isset($_POST['link']) ? $_POST['link'] : '';
    $email_subject = $_POST['email_subject'];
    $email_body = $_POST['email_body'];
    
    // Get template ID if available
    $template_id = isset($_POST['template_id']) ? $_POST['template_id'] : '';
    
    // Get custom fields if any
    $custom_fields = isset($_POST['custom_field']) ? $_POST['custom_field'] : [];
    
    // Use your existing send_custom_email function with current parameters
    $result = send_custom_email_with_template(
        $customer_email,
        $email_subject,
        $email_body,
        $customer_name,
        $vehicle_number,
        $total_premium,
        $link,
        $custom_fields
    );
    
    if ($result) {
        $_SESSION['message'] = "Email sent successfully to $customer_email";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Failed to send email. Please try again.";
        $_SESSION['message_type'] = "danger";
    }
    
    // Redirect back to the form
    header('Location: ../SentEmail.php');
    exit();
}

/**
 * Enhanced email sending function that works with the template form
 * 
 * @param string $to_email Recipient email
 * @param string $subject Email subject
 * @param string $body Email body
 * @param string $customer_name Customer name
 * @param string $vehicle_number Vehicle number
 * @param string $total_premium Total premium amount
 * @param string $link Any URL link
 * @param array $custom_fields Array of custom fields
 * @param int $signature_type Signature type (1-3)
 * @return bool True if email sent successfully, false otherwise
 */
function send_custom_email_with_template($to_email, $subject, $body, $customer_name = '', 
                                 $vehicle_number = '', $total_premium = '', $link = '', 
                                 $custom_fields = [], $signature_type = 1) {
    global $conn; // Use your existing DB connection
    
    // Static signatures - same as your original function
    $signatures = [
        1 => "<br><br>Best Regards,<br>PB Partners<br><a href='https://pbpartners.in'>Visit our website</a>",
        2 => "<br><br>Thanks & Regards,<br>Team Support<br>Contact: 1234567890",
        3 => "<br><br>Warm Regards,<br>Sales Team<br>PB Partners",
    ];
    
    // Add selected signature
    $signature = isset($signatures[$signature_type]) ? $signatures[$signature_type] : $signatures[1];
    $full_body = $body . $signature; // Full email content
    
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.hostinger.com';
        $mail->SMTPAuth = true;
        
        // Use the working email credentials
        $mail->Username = 'sarthi@sarthii.co.in';
        $mail->Password = 'K?69rn6j1I2c';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Set the sender and recipient
        $mail->setFrom('sarthi@sarthii.co.in', 'PB Partners');
        $mail->addAddress($to_email, $customer_name);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = nl2br($full_body);
        $mail->AltBody = strip_tags($full_body); // Plain text alternative
        
        // Send email
        if ($mail->send()) {
            // Insert Success Log
            if (isset($conn)) {
                $stmt = $conn->prepare("INSERT INTO email_logs (to_email, subject, body, status) VALUES (?, ?, ?, 'Success')");
                $stmt->bind_param("sss", $to_email, $subject, $full_body);
                $stmt->execute();
                $stmt->close();
            }
            return true;
        } else {
            // Insert Failed Log
            if (isset($conn)) {
                $stmt = $conn->prepare("INSERT INTO email_logs (to_email, subject, body, status, error_message) VALUES (?, ?, ?, 'Failed', ?)");
                $error = $mail->ErrorInfo;
                $stmt->bind_param("ssss", $to_email, $subject, $full_body, $error);
                $stmt->execute();
                $stmt->close();
            }
            error_log("Email sending error: " . $mail->ErrorInfo);
            return false;
        }
    } catch (Exception $e) {
        // Insert Exception Log
        if (isset($conn)) {
            $stmt = $conn->prepare("INSERT INTO email_logs (to_email, subject, body, status, error_message) VALUES (?, ?, ?, 'Failed', ?)");
            $error = $e->getMessage();
            $stmt->bind_param("ssss", $to_email, $subject, $full_body, $error);
            $stmt->execute();
            $stmt->close();
        }
        error_log("Email sending error: " . $e->getMessage());
        return false;
    }
}
?>