<!-- function/export_attendance_excel.php -->
<?php
require('../include/config.php');
require('../include/functions.inc.php');
require '../vendor/autoload.php'; // PhpSpreadsheet autoloader

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

session_start();

// Check session
if (!isset($_SESSION['USEROID'])) {
    die("Unauthorized access");
}

$employee_id = $_SESSION['USEROID'];
$month = isset($_GET['month']) ? intval($_GET['month']) : date('m');
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

$startDate = date("$year-$month-01");
$endDate = date("Y-m-t", strtotime($startDate));

// Fetch attendance data
$stmt = $conn->prepare("SELECT `date`, `status`, `check_in_time`, `check_out_time`, `total_hours`, `late_remark`
                        FROM `employee_attendance`
                        WHERE `employee_id` = ? AND `date` BETWEEN ? AND ?");
$stmt->bind_param("iss", $employee_id, $startDate, $endDate);
$stmt->execute();
$result = $stmt->get_result();

// Start spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set headers
$sheet->setCellValue('A1', 'Date')
      ->setCellValue('B1', 'Status')
      ->setCellValue('C1', 'Check In')
      ->setCellValue('D1', 'Check Out')
      ->setCellValue('E1', 'Total Hours');

$rowNum = 2;
while ($row = $result->fetch_assoc()) {
    $date = $row['date'];
    $status = ucfirst($row['status']);
    $lateRemark = $row['late_remark'];

    if ($status === 'Present') {
        switch ($lateRemark) {
            case 1: $statusTitle = 'Present (Late)'; break;
            case 0: $statusTitle = 'Present (On Time)'; break;
            case 2: $statusTitle = 'Half Day'; break;
            case 3: $statusTitle = 'Leave'; break;
            default: $statusTitle = 'Present';
        }
    } elseif ($status === 'Absent') {
        $statusTitle = 'Absent';
    } else {
        $statusTitle = 'Unknown';
    }

    $sheet->setCellValue("A$rowNum", $date);
    $sheet->setCellValue("B$rowNum", $statusTitle);
    $sheet->setCellValue("C$rowNum", $row['check_in_time'] ?? '--');
    $sheet->setCellValue("D$rowNum", $row['check_out_time'] ?? '--');
    $sheet->setCellValue("E$rowNum", $row['total_hours'] ?? '--');
    $rowNum++;
}

// Clear buffer before output
ob_end_clean();

// Output as Excel download
$filename = "Attendance_Report_{$month}_{$year}.xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>