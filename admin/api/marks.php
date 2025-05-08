<?php
session_start();
require_once '../../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Handle POST request (Create/Update marks)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        if (isset($data['id'])) {
            // Update existing marks
            $stmt = $pdo->prepare("
                UPDATE marks 
                SET student_id = ?, subject_id = ?, semester = ?, 
                    marks_obtained = ?, total_marks = ?, exam_date = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $data['student_id'],
                $data['subject_id'],
                $data['semester'],
                $data['marks_obtained'],
                $data['total_marks'],
                $data['exam_date'],
                $data['id']
            ]);
        } else {
            // Create new marks
            $stmt = $pdo->prepare("
                INSERT INTO marks (student_id, subject_id, semester, marks_obtained, total_marks, exam_date)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['student_id'],
                $data['subject_id'],
                $data['semester'],
                $data['marks_obtained'],
                $data['total_marks'],
                $data['exam_date']
            ]);
        }
        
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// Handle DELETE request
else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        $stmt = $pdo->prepare("DELETE FROM marks WHERE id = ?");
        $stmt->execute([$data['id']]);
        
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// Handle GET request
else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['id'])) {
        // Get single marks record
        $stmt = $pdo->prepare("
            SELECT m.*, s.full_name as student_name, sub.subject_name, sub.subject_code
            FROM marks m
            JOIN students s ON m.student_id = s.id
            JOIN subjects sub ON m.subject_id = sub.id
            WHERE m.id = ?
        ");
        $stmt->execute([$_GET['id']]);
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    } else {
        // Get all marks with student and subject details
        $stmt = $pdo->query("
            SELECT m.*, s.full_name as student_name, sub.subject_name, sub.subject_code
            FROM marks m
            JOIN students s ON m.student_id = s.id
            JOIN subjects sub ON m.subject_id = sub.id
            ORDER BY m.semester DESC, s.full_name, sub.subject_name
        ");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}
?> 