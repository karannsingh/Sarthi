<?php
// fetch_template.php
// Purpose: Fetch email template with custom fields

// Include database connection
require_once('../include/config.php');

// Check if template_id is provided
if (!isset($_POST['template_id']) || empty($_POST['template_id'])) {
    echo json_encode(['error' => 'Template ID is required']);
    exit;
}

$template_id = intval($_POST['template_id']);

// Prepare statement to get template details
$stmt = $conn->prepare("
    SELECT 
        name, 
        subject, 
        body, 
        signature_type, 
        custom_fields 
    FROM 
        email_templates 
    WHERE 
        id = ?
");

$stmt->bind_param("i", $template_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['error' => 'Template not found']);
    exit;
}

$template = $result->fetch_assoc();
$stmt->close();

// Get signature content based on signature_type
$signature = "";
switch ($template['signature_type']) {
    case 1:
        $signature = "<br><br>Best Regards,<br>Sarthi Enterprises<br><a href='https://sarthii.co.in'>Visit our website</a>";
        break;
    case 2:
        $signature = "<br><br>Thanks & Regards,<br>Team Support<br>Contact: 1234567890";
        break;
    case 3:
        $signature = "<br><br>Warm Regards,<br>Sales Team<br>Contact: 9876543210";
        break;
    default:
        $signature = "<br><br>Best Regards,<br>Sarthi Enterprises";
}

// Apply signature to the template body
$template['body'] = $template['body'] . $signature;

// Extract custom fields
$customFields = [];
if (!empty($template['custom_fields'])) {
    $customFields = json_decode($template['custom_fields'], true) ?: [];
}

// Prepare template fields info for frontend
$response = [
    'name' => $template['name'],
    'subject' => $template['subject'],
    'body' => $template['body'],
    'standard_vars' => [
        'customer_name' => '{customer_name}',
        'vehicle_number' => '{vehicle_number}',
        'total_premium' => '{total_premium}',
        'link' => '{link}'
    ],
    'custom_fields' => $customFields
];

echo json_encode($response);
exit;