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
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    // First, check if placement_sheets table exists, if not create it
    $createTableSql = "CREATE TABLE IF NOT EXISTS placement_sheets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        year INT NOT NULL UNIQUE,
        sheet_url TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($createTableSql);

    // Get all batches
    $sql = "SELECT year, sheet_url FROM placement_sheets ORDER BY year DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $batches = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'batches' => $batches
    ]);

} catch (PDOException $e) {
    error_log("Database error in admin_get_batches.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred',
        'batches' => []
    ]);
}
?>