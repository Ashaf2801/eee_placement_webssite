<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'eee_placement');

try {
    // Get parameters
    $registerNo = $_GET['register_no'] ?? null;
    
    if (!$registerNo) {
        throw new Exception('Register number parameter is required');
    }

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

    // Query to get student data
    $sql = "SELECT register_no, name, phone_no, mail, year_of_graduation 
            FROM student 
            WHERE register_no = :register_no";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':register_no', $registerNo, PDO::PARAM_STR);
    $stmt->execute();
    $student = $stmt->fetch();

    if ($student) {
        echo json_encode([
            'success' => true,
            'student' => $student
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Student not found',
            'student' => null
        ]);
    }

} catch (PDOException $e) {
    error_log("Database error in get_student_data.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed',
        'student' => null
    ]);
} catch (Exception $e) {
    error_log("General error in get_student_data.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'student' => null
    ]);
}
?>