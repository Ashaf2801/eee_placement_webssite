<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Change to your MySQL username
define('DB_PASS', ''); // Change to your MySQL password  
define('DB_NAME', 'eee_placement');

try {
    // Get batch parameter
    $batch = $_GET['batch'] ?? null;
    
    if (!$batch) {
        throw new Exception('Batch parameter is required');
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

    // Query to get companies for a specific batch with statistics
    $sql = "SELECT 
                p.company_name,
                COUNT(p.register_no) as student_count,
                AVG(p.package) as avg_package,
                MAX(p.package) as max_package
            FROM placement p 
            INNER JOIN student s ON p.register_no = s.register_no 
            WHERE s.year_of_graduation = :batch 
            GROUP BY p.company_name 
            ORDER BY avg_package DESC, student_count DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':batch', $batch, PDO::PARAM_INT);
    $stmt->execute();
    $companies = $stmt->fetchAll();

    if ($companies) {
        echo json_encode([
            'success' => true,
            'companies' => $companies,
            'batch' => $batch
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No companies found for this batch',
            'companies' => [],
            'batch' => $batch
        ]);
    }

} catch (PDOException $e) {
    error_log("Database error in get_companies.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed',
        'companies' => []
    ]);
} catch (Exception $e) {
    error_log("General error in get_companies.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'companies' => []
    ]);
}
?>