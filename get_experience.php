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
    $registerNo = $_GET['register_no'] ?? null;
    $company = $_GET['company'] ?? null;
    
    if (!$registerNo || !$company) {
        throw new Exception('Register number and company parameters are required');
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

    // Query to get experience details for a specific student and company
    $sql = "SELECT 
                p.round1,
                p.round2,
                p.round3,
                p.round4,
                p.round5,
                p.round6,
                p.company_name,
                p.company_type,
                p.package,
                p.is_placed,
                s.name
            FROM placement p 
            INNER JOIN student s ON p.register_no = s.register_no 
            WHERE p.register_no = :register_no AND p.company_name = :company";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':register_no', $registerNo, PDO::PARAM_STR);
    $stmt->bindParam(':company', $company, PDO::PARAM_STR);
    $stmt->execute();
    $experience = $stmt->fetch();

    if ($experience) {
        echo json_encode([
            'success' => true,
            'experience' => $experience
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No experience details found',
            'experience' => null
        ]);
    }

} catch (PDOException $e) {
    error_log("Database error in get_experience.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed',
        'experience' => null
    ]);
} catch (Exception $e) {
    error_log("General error in get_experience.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'experience' => null
    ]);
}
?>