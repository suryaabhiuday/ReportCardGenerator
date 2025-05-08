<?php
require_once 'config/database.php';

// New admin credentials
$username = 'admin';
$password = 'admin123';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    // Check if admin user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user) {
        // Update existing admin
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = ?");
        $stmt->execute([$hashed_password, $username]);
        echo "Admin password has been reset successfully!<br>";
    } else {
        // Create new admin
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'admin')");
        $stmt->execute([$username, $hashed_password]);
        echo "Admin user has been created successfully!<br>";
    }
    
    echo "You can now login with:<br>";
    echo "Username: admin<br>";
    echo "Password: admin123<br>";
    echo "<a href='index.php'>Go to Login Page</a>";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 