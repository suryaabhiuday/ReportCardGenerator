<?php
session_start();
require_once '../../config/database.php';
require_once '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../dashboard.php");
    exit();
}

function validateStudent($row) {
    $errors = [];
    if (empty($row['Username'])) $errors[] = "Username is required";
    if (empty($row['Password'])) $errors[] = "Password is required";
    if (empty($row['Full Name'])) $errors[] = "Full Name is required";
    if (empty($row['Roll Number'])) $errors[] = "Roll Number is required";
    if (empty($row['Class'])) $errors[] = "Class is required";
    if (empty($row['Section'])) $errors[] = "Section is required";
    return $errors;
}

function validateSubject($row) {
    $errors = [];
    if (empty($row['Subject Code'])) $errors[] = "Subject Code is required";
    if (empty($row['Subject Name'])) $errors[] = "Subject Name is required";
    return $errors;
}

function validateMarks($row) {
    $errors = [];
    if (empty($row['Roll Number'])) $errors[] = "Roll Number is required";
    if (empty($row['Subject Code'])) $errors[] = "Subject Code is required";
    if (empty($row['Semester'])) $errors[] = "Semester is required";
    if (!is_numeric($row['Marks Obtained'])) $errors[] = "Marks Obtained must be numeric";
    if (!is_numeric($row['Total Marks'])) $errors[] = "Total Marks must be numeric";
    if (empty($row['Exam Date'])) $errors[] = "Exam Date is required";
    return $errors;
}

try {
    if (!isset($_FILES['excelFile']) || $_FILES['excelFile']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Please select a valid Excel file");
    }

    $inputFileName = $_FILES['excelFile']['tmp_name'];
    $spreadsheet = IOFactory::load($inputFileName);
    
    $results = [
        'success' => true,
        'students' => ['imported' => 0, 'errors' => []],
        'subjects' => ['imported' => 0, 'errors' => []],
        'marks' => ['imported' => 0, 'errors' => []]
    ];

    $pdo->beginTransaction();

    // Process Students
    if (isset($_POST['import_students'])) {
        $studentsSheet = $spreadsheet->getSheetByName('Students');
        if ($studentsSheet) {
            $students = $studentsSheet->toArray();
            array_shift($students); // Remove header row
            
            foreach ($students as $index => $row) {
                $student = array_combine(['Username', 'Password', 'Full Name', 'Roll Number', 'Class', 'Section'], $row);
                $errors = validateStudent($student);
                
                if (empty($errors)) {
                    try {
                        // Create user
                        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'student')");
                        $stmt->execute([$student['Username'], password_hash($student['Password'], PASSWORD_DEFAULT)]);
                        $userId = $pdo->lastInsertId();
                        
                        // Create student
                        $stmt = $pdo->prepare("INSERT INTO students (user_id, full_name, roll_number, class, section) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([$userId, $student['Full Name'], $student['Roll Number'], $student['Class'], $student['Section']]);
                        
                        $results['students']['imported']++;
                    } catch (PDOException $e) {
                        $results['students']['errors'][] = "Row " . ($index + 2) . ": " . $e->getMessage();
                    }
                } else {
                    $results['students']['errors'][] = "Row " . ($index + 2) . ": " . implode(", ", $errors);
                }
            }
        }
    }

    // Process Subjects
    if (isset($_POST['import_subjects'])) {
        $subjectsSheet = $spreadsheet->getSheetByName('Subjects');
        if ($subjectsSheet) {
            $subjects = $subjectsSheet->toArray();
            array_shift($subjects); // Remove header row
            
            foreach ($subjects as $index => $row) {
                $subject = array_combine(['Subject Code', 'Subject Name'], $row);
                $errors = validateSubject($subject);
                
                if (empty($errors)) {
                    try {
                        $stmt = $pdo->prepare("INSERT INTO subjects (subject_code, subject_name) VALUES (?, ?)");
                        $stmt->execute([$subject['Subject Code'], $subject['Subject Name']]);
                        $results['subjects']['imported']++;
                    } catch (PDOException $e) {
                        $results['subjects']['errors'][] = "Row " . ($index + 2) . ": " . $e->getMessage();
                    }
                } else {
                    $results['subjects']['errors'][] = "Row " . ($index + 2) . ": " . implode(", ", $errors);
                }
            }
        }
    }

    // Process Marks
    if (isset($_POST['import_marks'])) {
        $marksSheet = $spreadsheet->getSheetByName('Marks');
        if ($marksSheet) {
            $marks = $marksSheet->toArray();
            array_shift($marks); // Remove header row
            
            foreach ($marks as $index => $row) {
                $mark = array_combine(['Roll Number', 'Subject Code', 'Semester', 'Marks Obtained', 'Total Marks', 'Exam Date'], $row);
                $errors = validateMarks($mark);
                
                if (empty($errors)) {
                    try {
                        // Get student_id
                        $stmt = $pdo->prepare("SELECT id FROM students WHERE roll_number = ?");
                        $stmt->execute([$mark['Roll Number']]);
                        $studentId = $stmt->fetchColumn();
                        
                        // Get subject_id
                        $stmt = $pdo->prepare("SELECT id FROM subjects WHERE subject_code = ?");
                        $stmt->execute([$mark['Subject Code']]);
                        $subjectId = $stmt->fetchColumn();
                        
                        if ($studentId && $subjectId) {
                            $stmt = $pdo->prepare("INSERT INTO marks (student_id, subject_id, semester, marks_obtained, total_marks, exam_date) VALUES (?, ?, ?, ?, ?, ?)");
                            $stmt->execute([$studentId, $subjectId, $mark['Semester'], $mark['Marks Obtained'], $mark['Total Marks'], $mark['Exam Date']]);
                            $results['marks']['imported']++;
                        } else {
                            $results['marks']['errors'][] = "Row " . ($index + 2) . ": Student or Subject not found";
                        }
                    } catch (PDOException $e) {
                        $results['marks']['errors'][] = "Row " . ($index + 2) . ": " . $e->getMessage();
                    }
                } else {
                    $results['marks']['errors'][] = "Row " . ($index + 2) . ": " . implode(", ", $errors);
                }
            }
        }
    }

    // If there are any errors, rollback the transaction
    if (!empty($results['students']['errors']) || !empty($results['subjects']['errors']) || !empty($results['marks']['errors'])) {
        $pdo->rollBack();
        $results['success'] = false;
    } else {
        $pdo->commit();
    }

    // Store results in session for display
    $_SESSION['import_results'] = $results;
    header("Location: import.php");
    exit();

} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    $_SESSION['import_error'] = $e->getMessage();
    header("Location: import.php");
    exit();
} 