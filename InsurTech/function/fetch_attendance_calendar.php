<?php
require('../include/config.php');
require('../include/functions.inc.php');
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['USEROID'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$employee_id = $_SESSION['USEROID'];
$today = date('Y-m-d');

// Get holidays for the selected month
function getHolidays($conn, $month, $year) {
    $startDate = date("$year-$month-01");
    $endDate = date("Y-m-t", strtotime($startDate));
    
    $stmt = $conn->prepare("SELECT `date`, `name`, `description`, `is_public` FROM `holidays` WHERE `date` BETWEEN ? AND ?");
    $stmt->bind_param("ss", $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $holidays = [];
    while ($row = $result->fetch_assoc()) {
        $holidays[$row['date']] = $row;
    }
    
    return $holidays;
}

// Get joining date
$empStmt = $conn->prepare("SELECT JoiningDt, Designation FROM users WHERE UserOID = ?");
$empStmt->bind_param("i", $employee_id);
$empStmt->execute();
$empResult = $empStmt->get_result();
if ($empResult->num_rows === 0) {
    echo json_encode([]);
    exit;
}
$employee = $empResult->fetch_assoc();
$joiningDate = $employee['JoiningDt'] ?? date('Y-m-01');
$userRole = $employee['Designation'] ?? 'employee';

// GET month/year from URL or use current month
$month = isset($_GET['month']) ? intval($_GET['month']) : intval(date('m'));
$year = isset($_GET['year']) ? intval($_GET['year']) : intval(date('Y'));

// Set start date as 1st of selected month
$startDate = date("$year-$month-01");
$endDate = date("Y-m-t", strtotime($startDate));

// Don't allow selection of date range before joining date
if ($startDate < $joiningDate) {
    $startDate = $joiningDate;
}

// Fetch holidays for the month
$holidays = getHolidays($conn, $month, $year);

// Fetch pending regularization requests
$pendingRequests = [];
$regStmt = $conn->prepare("SELECT `date`, `type`, `status` FROM `attendance_regularizations` 
                           WHERE `employee_id` = ? AND `date` BETWEEN ? AND ?");
$regStmt->bind_param("iss", $employee_id, $startDate, $endDate);
$regStmt->execute();
$regResult = $regStmt->get_result();
while ($row = $regResult->fetch_assoc()) {
    $pendingRequests[$row['date']] = $row;
}

// Fetch all attendance records for the selected month
$stmt = $conn->prepare("SELECT 
                        ea.`date`, ea.`status`, ea.`check_in_time`, ea.`check_out_time`,
                        ea.`total_hours`, ea.`checkin_latitude`, ea.`checkin_longitude`,
                        ea.`checkout_latitude`, ea.`checkout_longitude`, ea.`late_remark`,
                        reg.`id` as regularization_id, reg.`status` as regularization_status,
                        reg.`type` as regularization_type, reg.`reason` as regularization_reason
                        FROM `employee_attendance` ea
                        LEFT JOIN `attendance_regularizations` reg ON ea.`date` = reg.`date` AND ea.`employee_id` = reg.`employee_id`
                        WHERE ea.`employee_id` = ? AND ea.`date` BETWEEN ? AND ?");
$stmt->bind_param("iss", $employee_id, $startDate, $endDate);
$stmt->execute();
$result = $stmt->get_result();

$records = [];
while ($row = $result->fetch_assoc()) {
    $records[$row['date']] = $row;
    
    // Check if there's a pending regularization for this date
    if (isset($pendingRequests[$row['date']])) {
        $records[$row['date']]['regularization_status'] = $pendingRequests[$row['date']]['status'];
        $records[$row['date']]['regularization_type'] = $pendingRequests[$row['date']]['type'];
    }
}

// Format calendar data
$calendarData = [];
$currentDate = new DateTime($startDate);
$lastDay = new DateTime($endDate);

while ($currentDate <= $lastDay) {
    $dateStr = $currentDate->format('Y-m-d');
    $event = [
        'id' => $currentDate->format('Ymd'),
        'start' => $dateStr,
        'end' => $dateStr,
        'allDay' => true
    ];
    
    // Check if it's a holiday
    if (isset($holidays[$dateStr])) {
        $event['title'] = $holidays[$dateStr]['name'];
        $event['color'] = '#007bff'; // Blue for holidays
        $event['extendedProps'] = [
            'type' => 'holiday',
            'description' => $holidays[$dateStr]['description']
        ];
    } 
    // Check if there's attendance data for this date
    else if (isset($records[$dateStr])) {
        $record = $records[$dateStr];
        
        // Check if there's a pending regularization
        if (isset($record['regularization_status']) && $record['regularization_status'] == 'pending') {
            $event['title'] = 'Regularization Pending';
            $event['color'] = '#adb5bd'; // Grey for pending
            $event['extendedProps'] = [
                'type' => 'regularization',
                'status' => 'pending',
                'regularizationType' => $record['regularization_type'] ?? '',
                'checkIn' => $record['check_in_time'] ? date('h:i A', strtotime($record['check_in_time'])) : '--',
                'checkOut' => $record['check_out_time'] ? date('h:i A', strtotime($record['check_out_time'])) : '--',
                'totalHours' => $record['total_hours'] ?? '--',
                'CheckInlocation' => $record['checkin_latitude'] && $record['checkin_longitude'] ? 
                    "https://maps.google.com/?q={$record['checkin_latitude']},{$record['checkin_longitude']}" : null,
                'CheckOutlocation' => $record['checkout_latitude'] && $record['checkout_longitude'] ? 
                    "https://maps.google.com/?q={$record['checkout_latitude']},{$record['checkout_longitude']}" : null
            ];
        } 
        // Regular attendance
        else {
            // Determine attendance status and color
            switch ($record['status']) {
                case 'Present':
                    if ($record['late_remark'] == 1) {
                        $event['title'] = 'Late Arrival';
                        $event['color'] = '#fd7e14'; // Orange for late
                    } else {
                        $event['title'] = 'Present';
                        $event['color'] = '#28a745'; // Green for present
                    }
                    break;
                case 'Half Day':
                    $event['title'] = 'Half Day';
                    $event['color'] = '#fd7e14'; // Orange for half day
                    break;
                case 'Leave':
                    $event['title'] = 'Leave';
                    $event['color'] = '#6610f2'; // Purple for leave
                    break;
                default:
                    $event['title'] = 'Absent';
                    $event['color'] = '#dc3545'; // Red for absent
            }

            $event['extendedProps'] = [
                'type' => 'attendance',
                'checkIn' => $record['check_in_time'] ? date('h:i A', strtotime($record['check_in_time'])) : '--',
                'checkOut' => $record['check_out_time'] ? date('h:i A', strtotime($record['check_out_time'])) : '--',
                'totalHours' => $record['total_hours'] ?? '--',
                'CheckInlocation' => $record['checkin_latitude'] && $record['checkin_longitude'] ? 
                    "https://maps.google.com/?q={$record['checkin_latitude']},{$record['checkin_longitude']}" : null,
                'CheckOutlocation' => $record['checkout_latitude'] && $record['checkout_longitude'] ? 
                    "https://maps.google.com/?q={$record['checkout_latitude']},{$record['checkout_longitude']}" : null
            ];
        }
    } 
    // Future date or no records
    else if ($dateStr > $today) {
        $event['title'] = '';
        $event['color'] = '#adb5bd'; // Grey for upcoming
        $event['extendedProps'] = [
            'type' => 'upcoming'
        ];
    } 
    // Past date with no records (absent)
    else {
        $event['title'] = 'Absent';
        $event['color'] = '#dc3545'; // Red for absent
        $event['extendedProps'] = [
            'type' => 'absence',
            'checkIn' => '--',
            'checkOut' => '--',
            'totalHours' => '--'
        ];
    }

    $calendarData[] = $event;
    $currentDate->modify('+1 day');
}

echo json_encode($calendarData);
?>