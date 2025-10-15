<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Change to your MySQL username
define('DB_PASS', ''); // Change to your MySQL password  
define('DB_NAME', 'eee_placement'); // Updated database name

try {
    // Create database connection
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

    // Query to get batches with placement count
    $sql = "SELECT s.year_of_graduation, COUNT(DISTINCT p.register_no) as student_count
            FROM student s 
            INNER JOIN placement p ON s.register_no = p.register_no 
            GROUP BY s.year_of_graduation 
            ORDER BY s.year_of_graduation DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $batches = $stmt->fetchAll();

    if ($batches) {
        echo json_encode([
            'success' => true,
            'batches' => $batches
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No placement data found',
            'batches' => []
        ]);
    }

} catch (PDOException $e) {
    error_log("Database error in get_batches.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed',
        'batches' => []
    ]);
} catch (Exception $e) {
    error_log("General error in get_batches.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred',
        'batches' => []
    ]);
}
?>