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
    $userId = trim($_POST['userId'] ?? '');

    if (empty($userId)) {
        throw new Exception('User ID is required');
    }

    // Prevent deleting yourself
    if ($userId === $_SESSION['user_id']) {
        throw new Exception('You cannot delete your own account');
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

    $sql = "DELETE FROM user WHERE user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'User not found'
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>