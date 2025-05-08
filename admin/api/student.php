<?php
session_start();
require_once '../../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Handle POST request (Create/Update student)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        $pdo->beginTransaction();
        
        if (isset($data['id'])) {
            // Update existing student
            $stmt = $pdo->prepare("
                UPDATE students 
                SET full_name = ?, roll_number = ?, class = ?, section = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $data['full_name'],
                $data['roll_number'],
                $data['class'],
                $data['section'],
                $data['id']
            ]);
            
            // Update user credentials if provided
            if (!empty($data['password'])) {
                $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET username = ?, password = ?
                    WHERE id = (SELECT user_id FROM students WHERE id = ?)
                ");
                $stmt->execute([$data['username'], $hashed_password, $data['id']]);
            }
        } else {
            // Create new user
            $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                INSERT INTO users (username, password, role)
                VALUES (?, ?, 'student')
            ");
            $stmt->execute([$data['username'], $hashed_password]);
            $user_id = $pdo->lastInsertId();
            
            // Create new student
            $stmt = $pdo->prepare("
                INSERT INTO students (user_id, full_name, roll_number, class, section)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $user_id,
                $data['full_name'],
                $data['roll_number'],
                $data['class'],
                $data['section']
            ]);
        }
        
        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// Handle DELETE request
else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        $pdo->beginTransaction();
        
        // Get user_id before deleting student
        $stmt = $pdo->prepare("SELECT user_id FROM students WHERE id = ?");
        $stmt->execute([$data['id']]);
        $user_id = $stmt->fetchColumn();
        
        // Delete student
        $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
        $stmt->execute([$data['id']]);
        
        // Delete user
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        
        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// Handle GET request
else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['id'])) {
        // Get single student
        $stmt = $pdo->prepare("
            SELECT s.*, u.username 
            FROM students s 
            JOIN users u ON s.user_id = u.id 
            WHERE s.id = ?
        ");
        $stmt->execute([$_GET['id']]);
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    } else {
        // Get all students
        $stmt = $pdo->query("
            SELECT s.*, u.username 
            FROM students s 
            JOIN users u ON s.user_id = u.id
        ");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}
?> 