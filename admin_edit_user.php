<?php
session_start();
header('Content-Type: application/json');

// Check if user is admin or faculty
if (!isset($_SESSION['mail_id']) || !in_array($_SESSION['user_type'], ['admin', 'faculty'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'eee_placement');

try {
    $mailId = trim($_POST['editUserId'] ?? ''); // This is now mail_id
    $userName = trim($_POST['editUserName'] ?? '');
    $userType = $_POST['editUserType'] ?? '';
    $newPassword = $_POST['editUserPassword'] ?? '';

    if (empty($mailId) || empty($userName) || empty($userType)) {
        throw new Exception('User ID, Name, and Type are required');
    }

    if (!in_array($userType, ['student', 'faculty', 'admin'])) {
        throw new Exception('Invalid user type');
    }

    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    // Update user
    if (!empty($newPassword)) {
        $sql = "UPDATE users SET user_name = :user_name, user_type = :user_type, password = :password WHERE mail_id = :mail_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_name', $userName);
        $stmt->bindParam(':user_type', $userType);
        $stmt->bindParam(':password', $newPassword);
        $stmt->bindParam(':mail_id', $mailId);
    } else {
        $sql = "UPDATE users SET user_name = :user_name, user_type = :user_type WHERE mail_id = :mail_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_name', $userName);
        $stmt->bindParam(':user_type', $userType);
        $stmt->bindParam(':mail_id', $mailId);
    }

    $stmt->execute();

    echo json_encode([
        'success' => true,
        'message' => 'User updated successfully'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>