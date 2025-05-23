<?php
ob_start();

// Include only what's needed for database
require("include/config.php");

// Include the spreadsheet library
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Border;

// Create new spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Define headers for Vehicle Data template
$headers = [
    'Policy Number', 'Customer Name', 'Email', 'Mobile Number', 
    'Renewal Date', 'IDV', 'Premium', 'NCB', 
    'Engine Number', 'Chassis Number', 'Vehicle Number', 
    'Vehicle Model', 'Manufacturing Year'
];

// Add headers to first row
foreach ($headers as $index => $header) {
    $column = chr(65 + $index); // A, B, C, etc.
    $cell = $column . '1';

    $sheet->setCellValue($cell, $header);

    // Style the header cell
    $sheet->getStyle($cell)->applyFromArray([
        'font' => [
            'bold' => true,
            'name' => 'Calibri Light',
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => [
                'rgb' => 'FFFF00', // Yellow
            ],
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => '000000'], // Black border
            ],
        ],
    ]);

    $sheet->getColumnDimension($column)->setAutoSize(true);
}

// Increase Row 1 Height slightly for better looks (optional)
$sheet->getRowDimension(1)->setRowHeight(25);

// Clear any previous output
ob_end_clean();

// Set content type and headers for download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="vehicle_data_template.xlsx"');
header('Cache-Control: max-age=0');

// Save to PHP output
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>