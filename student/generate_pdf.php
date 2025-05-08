<?php
// Prevent any output before PDF generation
ob_start();

session_start();
require_once '../config/database.php';
require_once '../vendor/autoload.php'; // You'll need to install TCPDF via Composer

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../index.php");
    exit();
}

if (!isset($_POST['semester'])) {
    header("Location: dashboard.php");
    exit();
}

$semester = $_POST['semester'];

// Fetch student details
$stmt = $pdo->prepare("SELECT s.* FROM students s WHERE s.user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$student = $stmt->fetch();

// Fetch marks for the selected semester
$stmt = $pdo->prepare("
    SELECT m.*, s.subject_name, s.subject_code 
    FROM marks m 
    JOIN subjects s ON m.subject_id = s.id 
    WHERE m.student_id = ? AND m.semester = ?
    ORDER BY s.subject_name
");
$stmt->execute([$student['id'], $semester]);
$marks = $stmt->fetchAll();

// Calculate total marks and percentage
$total_obtained = 0;
$total_max = 0;
foreach ($marks as $mark) {
    $total_obtained += $mark['marks_obtained'];
    $total_max += $mark['total_marks'];
}
$overall_percentage = ($total_max > 0) ? ($total_obtained / $total_max) * 100 : 0;

// Calculate grade
$grade = '';
if ($overall_percentage >= 90) $grade = 'A+';
elseif ($overall_percentage >= 80) $grade = 'A';
elseif ($overall_percentage >= 70) $grade = 'B';
elseif ($overall_percentage >= 60) $grade = 'C';
elseif ($overall_percentage >= 50) $grade = 'D';
else $grade = 'F';

// Create new PDF document
class MYPDF extends TCPDF {
    public function Header() {
        $this->SetFont('helvetica', 'B', 20);
        $this->Cell(0, 15, 'Report Card', 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }
}

$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('School Name');
$pdf->SetTitle('Report Card - ' . $student['full_name']);

// Set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

// Set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// Set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Add a page
$pdf->AddPage();

// Set font
$pdf->SetFont('helvetica', '', 12);

// Student Information
$pdf->Ln(20);
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Student Information', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 12);

$pdf->Cell(40, 7, 'Name:', 0);
$pdf->Cell(0, 7, $student['full_name'], 0, 1);

$pdf->Cell(40, 7, 'Roll Number:', 0);
$pdf->Cell(0, 7, $student['roll_number'], 0, 1);

$pdf->Cell(40, 7, 'Class:', 0);
$pdf->Cell(0, 7, $student['class'] . ' ' . $student['section'], 0, 1);

$pdf->Cell(40, 7, 'Semester:', 0);
$pdf->Cell(0, 7, $semester, 0, 1);

// Marks Table
$pdf->Ln(10);
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Academic Performance', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 12);

// Table header
$pdf->SetFillColor(240, 240, 240);
$pdf->Cell(30, 7, 'Subject Code', 1, 0, 'C', true);
$pdf->Cell(60, 7, 'Subject Name', 1, 0, 'C', true);
$pdf->Cell(30, 7, 'Marks', 1, 0, 'C', true);
$pdf->Cell(30, 7, 'Total', 1, 0, 'C', true);
$pdf->Cell(40, 7, 'Percentage', 1, 1, 'C', true);

// Table data
foreach ($marks as $mark) {
    $percentage = ($mark['marks_obtained'] / $mark['total_marks']) * 100;
    $pdf->Cell(30, 7, $mark['subject_code'], 1);
    $pdf->Cell(60, 7, $mark['subject_name'], 1);
    $pdf->Cell(30, 7, $mark['marks_obtained'], 1, 0, 'C');
    $pdf->Cell(30, 7, $mark['total_marks'], 1, 0, 'C');
    $pdf->Cell(40, 7, number_format($percentage, 2) . '%', 1, 1, 'C');
}

// Overall Performance
$pdf->Ln(10);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(120, 7, 'Overall Performance:', 0);
$pdf->Cell(0, 7, number_format($overall_percentage, 2) . '%', 0, 1);

// Grade
$pdf->Cell(120, 7, 'Grade:', 0);
$pdf->Cell(0, 7, $grade, 0, 1);

// Clear any output buffer
ob_end_clean();

// Output PDF
$pdf->Output('Report_Card_' . $student['roll_number'] . '_Sem' . $semester . '.pdf', 'D');
?> 