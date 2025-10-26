<?php
session_start();
header('Content-Type: application/json');

// Check if user is admin or faculty
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['db_user_type'] ?? $_SESSION['user_type'], ['admin', 'faculty'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'eee_placement');

try {
    $userId = trim($_POST['editUserId'] ?? '');
    $userType = $_POST['editUserType'] ?? '';
    $newPassword = $_POST['editUserPassword'] ?? '';

    if (empty($userId) || empty($userType)) {
        throw new Exception('User ID and type are required');
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
        $sql = "UPDATE user SET user_type = :user_type, password = :password WHERE user_id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_type', $userType);
        $stmt->bindParam(':password', $newPassword);
        $stmt->bindParam(':user_id', $userId);
    } else {
        $sql = "UPDATE user SET user_type = :user_type WHERE user_id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_type', $userType);
        $stmt->bindParam(':user_id', $userId);
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