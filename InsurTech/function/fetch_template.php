<!-- function/fetch_template.php -->
<?php
require_once '../include/config.php';

header('Content-Type: application/json');

// Check if template ID is provided
if (isset($_POST['template_id']) && !empty($_POST['template_id'])) {
    $template_id = (int)$_POST['template_id'];
    
    // Prepare statement
    $stmt = $conn->prepare("SELECT subject, body, signature_type, custom_fields FROM email_templates WHERE id = ?");
    $stmt->bind_param("i", $template_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        
        // Add custom fields to the response
        $custom_fields = [];
        if (!empty($data['custom_fields'])) {
            $custom_fields = json_decode($data['custom_fields'], true);
        }
        
        // Create response object
        $response = [
            'subject' => $data['subject'],
            'body' => $data['body'],
            'signature_type' => $data['signature_type'],
            'custom_fields' => $custom_fields
        ];
        
        echo json_encode($response);
    } else {
        echo json_encode(['error' => 'Template not found']);
    }
} else {
    echo json_encode(['error' => 'Invalid request']);
}