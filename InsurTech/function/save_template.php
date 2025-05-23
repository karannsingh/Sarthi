<!-- function/save_template.php -->
<?php
require_once '../include/config.php';
session_start();

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    $template_name = trim($_POST['template_name'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $body = trim($_POST['body'] ?? '');
    $signature_type = (int)($_POST['signature_type'] ?? 1);
    
    // Validate fields
    if (empty($template_name) || empty($subject) || empty($body)) {
        $_SESSION['message'] = 'All required fields must be filled out.';
        $_SESSION['message_type'] = 'danger';
        header('Location: ../create_template.php');
        exit;
    }
    
    // Process custom variables if any
    $custom_fields = [];
    if (isset($_POST['custom_vars']) && is_array($_POST['custom_vars'])) {
        foreach ($_POST['custom_vars'] as $field) {
            if (!empty($field['name']) && !empty($field['label'])) {
                // Clean variable name (no spaces, special chars)
                $var_name = preg_replace('/[^a-zA-Z0-9_]/', '', $field['name']);
                
                if (!empty($var_name)) {
                    $custom_fields[] = [
                        'name' => $var_name,
                        'label' => trim($field['label'])
                    ];
                }
            }
        }
    }
    
    // Encode custom fields as JSON
    $custom_fields_json = !empty($custom_fields) ? json_encode($custom_fields) : null;
    
    try {
        // Prepare and execute the insertion
        $stmt = $conn->prepare("INSERT INTO email_templates (name, subject, body, signature_type, custom_fields) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssis", $template_name, $subject, $body, $signature_type, $custom_fields_json);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = 'Email template created successfully!';
            $_SESSION['message_type'] = 'success';
            header('Location: ../manage_templates.php');
            exit;
        } else {
            throw new Exception("Database error: " . $conn->error);
        }
        
    } catch (Exception $e) {
        $_SESSION['message'] = 'Error creating template: ' . $e->getMessage();
        $_SESSION['message_type'] = 'danger';
        header('Location: ../create_template.php');
        exit;
    }
    
} else {
    // If not a POST request, redirect to the form
    header('Location: ../create_template.php');
    exit;
}
?>