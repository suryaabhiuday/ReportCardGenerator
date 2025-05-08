<?php
session_start();
require_once '../../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Handle POST request (Create/Update subject)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        if (isset($data['id'])) {
            // Update existing subject
            $stmt = $pdo->prepare("
                UPDATE subjects 
                SET subject_name = ?, subject_code = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $data['subject_name'],
                $data['subject_code'],
                $data['id']
            ]);
        } else {
            // Create new subject
            $stmt = $pdo->prepare("
                INSERT INTO subjects (subject_name, subject_code)
                VALUES (?, ?)
            ");
            $stmt->execute([
                $data['subject_name'],
                $data['subject_code']
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
        // Check if subject has any marks
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM marks WHERE subject_id = ?");
        $stmt->execute([$data['id']]);
        if ($stmt->fetchColumn() > 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Cannot delete subject with existing marks']);
            exit();
        }
        
        // Delete subject
        $stmt = $pdo->prepare("DELETE FROM subjects WHERE id = ?");
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
        // Get single subject
        $stmt = $pdo->prepare("SELECT * FROM subjects WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    } else {
        // Get all subjects
        $stmt = $pdo->query("SELECT * FROM subjects ORDER BY subject_name");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}
?> 