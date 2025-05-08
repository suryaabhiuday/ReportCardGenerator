<?php
session_start();
require_once '../../config/database.php';
require_once '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../dashboard.php");
    exit();
}

// Create new spreadsheet
$spreadsheet = new Spreadsheet();

// Students Sheet
$studentsSheet = $spreadsheet->getActiveSheet();
$studentsSheet->setTitle('Students');
$studentsSheet->fromArray([
    ['Username', 'Password', 'Full Name', 'Roll Number', 'Class', 'Section'],
    ['student1', 'password123', 'John Doe', 'R001', '10', 'A'],
    ['student2', 'password123', 'Jane Smith', 'R002', '10', 'A']
], null, 'A1');

// Subjects Sheet
$subjectsSheet = $spreadsheet->createSheet();
$subjectsSheet->setTitle('Subjects');
$subjectsSheet->fromArray([
    ['Subject Code', 'Subject Name'],
    ['MATH', 'Mathematics'],
    ['SCI', 'Science']
], null, 'A1');

// Marks Sheet
$marksSheet = $spreadsheet->createSheet();
$marksSheet->setTitle('Marks');
$marksSheet->fromArray([
    ['Roll Number', 'Subject Code', 'Semester', 'Marks Obtained', 'Total Marks', 'Exam Date'],
    ['R001', 'MATH', '1', '85', '100', '2024-03-15'],
    ['R001', 'SCI', '1', '90', '100', '2024-03-15']
], null, 'A1');

// Set column widths
foreach (range('A', 'F') as $col) {
    $studentsSheet->getColumnDimension($col)->setAutoSize(true);
    $marksSheet->getColumnDimension($col)->setAutoSize(true);
}
foreach (range('A', 'B') as $col) {
    $subjectsSheet->getColumnDimension($col)->setAutoSize(true);
}

// Add data validation and formatting
$validation = $studentsSheet->getCell('A2')->getDataValidation();
$validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_CUSTOM);
$validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
$validation->setAllowBlank(false);
$validation->setShowInputMessage(true);
$validation->setShowErrorMessage(true);
$validation->setShowDropDown(true);
$validation->setFormula1('=INDIRECT("Subjects!A:A")');

// Create the Excel file
$writer = new Xlsx($spreadsheet);

// Clear any previous output
ob_clean();

// Set headers for download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="import_template.xlsx"');
header('Cache-Control: max-age=0');
header('Cache-Control: must-revalidate');
header('Pragma: public');

// Save file to PHP output
$writer->save('php://output');
exit; 