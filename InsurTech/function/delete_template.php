<!-- function/delete_template.php -->
<?php
require_once '../include/config.php';

// Check if template ID is posted
if (isset($_POST['template_id']) && !empty($_POST['template_id'])) {
    $template_id = (int)$_POST['template_id'];
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Check if the template exists
        $check = $conn->prepare("SELECT id FROM email_templates WHERE id = ?");
        $check->bind_param("i", $template_id);
        $check->execute();
        $result = $check->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Template not found");
        }
        
        // Delete the template
        $delete = $conn->prepare("DELETE FROM email_templates WHERE id = ?");
        $delete->bind_param("i", $template_id);
        
        if (!$delete->execute()) {
            throw new Exception("Failed to delete template");
        }
        
        // Commit the transaction
        $conn->commit();
        
        session_start();
        $_SESSION['message'] = 'Template deleted successfully!';
        $_SESSION['message_type'] = 'success';
        
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        
        session_start();
        $_SESSION['message'] = 'Error: ' . $e->getMessage();
        $_SESSION['message_type'] = 'danger';
    }
} else {
    session_start();
    $_SESSION['message'] = 'Invalid request';
    $_SESSION['message_type'] = 'danger';
}

// Redirect back to the templates page
header('Location: ../manage_templates.php');
exit;

?>