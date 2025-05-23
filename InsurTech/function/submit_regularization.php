<?php
require('../include/config.php');
require('../include/functions.inc.php');
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['USEROID'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$employee_id = $_SESSION['USEROID'];

// Validate inputs
if (empty($_POST['date']) || empty($_POST['type']) || empty($_POST['reason'])) {
    echo json_encode(['success' => false, 'message' => 'Required fields are missing']);
    exit;
}

// Get inputs
$date = $_POST['date'];
$type = $_POST['type'];
$reason = $_POST['reason'];
$check_in_time = isset($_POST['check_in_time']) ? $_POST['check_in_time'] : null;
$check_out_time = isset($_POST['check_out_time']) ? $_POST['check_out_time'] : null;

// Validate date format and ensure not future date
$today = date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || $date > $today) {
    echo json_encode(['success' => false, 'message' => 'Invalid date']);
    exit;
}

// Validate regularization type
$validTypes = ['checkin_correction', 'checkout_correction', 'forgot', 'leave', 'half_day'];
if (!in_array($type, $validTypes)) {
    echo json_encode(['success' => false, 'message' => 'Invalid regularization type']);
    exit;
}

// Handle file upload if present
$evidence_path = null;
if (isset($_FILES['evidence']) && $_FILES['evidence']['error'] == 0) {
    $allowed = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
    $filename = $_FILES['evidence']['name'];
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    
    if (!in_array(strtolower($ext), $allowed)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type']);
        exit;
    }
    
    // Create unique filename
    $newFilename = 'evidence_' . $employee_id . '_' . date('Ymd_His') . '.' . $ext;
    $upload_dir = '../uploads/regularization/';
    
    // Create directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Move uploaded file
    if (move_uploaded_file($_FILES['evidence']['tmp_name'], $upload_dir . $newFilename)) {
        $evidence_path = 'uploads/regularization/' . $newFilename;
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
        exit;
    }
}

// Calculate new total hours if both check-in and check-out are provided
$total_hours = null;
if ($check_in_time && $check_out_time) {
    $check_in = new DateTime($check_in_time);
    $check_out = new DateTime($check_out_time);
    
    if ($check_out < $check_in) {
        echo json_encode(['success' => false, 'message' => 'Check-out time cannot be earlier than check-in time']);
        exit;
    }
    
    $interval = $check_in->diff($check_out);
    $total_hours = $interval->h + ($interval->i / 60);
    $total_hours = number_format($total_hours, 2);
}

// Get employee's manager/team leader ID for approval routing
$managerStmt = $conn->prepare("SELECT manager_id FROM users WHERE UserOID = ?");
$managerStmt->bind_param("i", $employee_id);
$managerStmt->execute();
$managerResult = $managerStmt->get_result();
$manager_id = null;

if ($managerResult->num_rows > 0) {
    $manager = $managerResult->fetch_assoc();
    $manager_id = $manager['manager_id'];
}

// Start transaction
$conn->begin_transaction();

try {
    // Check if a regularization request already exists for this date
    $checkStmt = $conn->prepare("SELECT id FROM attendance_regularizations WHERE employee_id = ? AND date = ?");
    $checkStmt->bind_param("is", $employee_id, $date);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        // Update existing request
        $row = $checkResult->fetch_assoc();
        $regId = $row['id'];
        
        $updateStmt = $conn->prepare("UPDATE attendance_regularizations SET 
                                     type = ?, 
                                     reason = ?, 
                                     check_in_time = ?, 
                                     check_out_time = ?, 
                                     total_hours = ?, 
                                     evidence = ?, 
                                     manager_id = ?, 
                                     status = 'pending', 
                                     updated_at = NOW() 
                                     WHERE id = ?");
        $updateStmt->bind_param("sssssisi", $type, $reason, $check_in_time, $check_out_time, $total_hours, $evidence_path, $manager_id, $regId);
        $updateStmt->execute();
    } else {
        // Insert new regularization request
        $insertStmt = $conn->prepare("INSERT INTO attendance_regularizations 
                                     (employee_id, date, type, reason, check_in_time, check_out_time, 
                                      total_hours, evidence, manager_id, status, created_at) 
                                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");
        $insertStmt->bind_param("isssssssi", $employee_id, $date, $type, $reason, $check_in_time, $check_out_time, $total_hours, $evidence_path, $manager_id);
        $insertStmt->execute();
    }
    
    // Commit the transaction
    $conn->commit();
    
    // Send notification to manager if we have one
    if ($manager_id) {
        // This would integrate with your notification system
        // For now, let's just log it
        $notificationStmt = $conn->prepare("INSERT INTO notifications 
                                          (user_id, title, message, link, created_at) 
                                          VALUES (?, 'Attendance Regularization Request', ?, 'attendance_approvals.php', NOW())");
        $message = "Employee " . $_SESSION['USERNAME'] . " has requested attendance regularization for " . date('d M Y', strtotime($date));
        $notificationStmt->bind_param("is", $manager_id, $message);
        $notificationStmt->execute();
    }
    
    echo json_encode(['success' => true, 'message' => 'Regularization request submitted successfully']);
    
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}