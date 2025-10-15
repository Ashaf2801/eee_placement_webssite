<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Change to your MySQL username
define('DB_PASS', ''); // Change to your MySQL password  
define('DB_NAME', 'eee_placement');

try {
    // Get parameters
    $batch = $_GET['batch'] ?? null;
    $company = $_GET['company'] ?? null;
    
    if (!$batch || !$company) {
        throw new Exception('Batch and company parameters are required');
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

    // Query to get students for a specific company and batch
    $sql = "SELECT 
                s.register_no,
                s.name,
                s.phone_no,
                s.mail,
                p.package
            FROM student s 
            INNER JOIN placement p ON s.register_no = p.register_no 
            WHERE s.year_of_graduation = :batch AND p.company_name = :company 
            ORDER BY s.name ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':batch', $batch, PDO::PARAM_INT);
    $stmt->bindParam(':company', $company, PDO::PARAM_STR);
    $stmt->execute();
    $students = $stmt->fetchAll();

    if ($students) {
        echo json_encode([
            'success' => true,
            'students' => $students,
            'batch' => $batch,
            'company' => $company
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No students found for this company',
            'students' => [],
            'batch' => $batch,
            'company' => $company
        ]);
    }

} catch (PDOException $e) {
    error_log("Database error in get_students.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed',
        'students' => []
    ]);
} catch (Exception $e) {
    error_log("General error in get_students.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'students' => []
    ]);
}
?>