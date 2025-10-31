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
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    $sql = "SELECT mail_id, user_name, user_type FROM users ORDER BY user_type, mail_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $users = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'users' => $users
    ]);

} catch (PDOException $e) {
    error_log("Database error in admin_get_users.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred',
        'users' => []
    ]);
}
?>