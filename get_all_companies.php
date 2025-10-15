<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'eee_placement');

try {
    // Create database connection with timeout settings
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 5 // 5 second timeout
        ]
    );

    // Simple query to get all companies quickly
    $sql = "SELECT company_name FROM company ORDER BY company_name ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $companies = $stmt->fetchAll();

    // If no companies exist, create some default ones
    if (empty($companies)) {
        $defaultCompanies = ['TCS', 'Infosys', 'Wipro', 'Accenture', 'Microsoft', 'Google', 'Amazon'];
        
        foreach ($defaultCompanies as $company) {
            $insertSQL = "INSERT IGNORE INTO company (company_name) VALUES (?)";
            $insertStmt = $pdo->prepare($insertSQL);
            $insertStmt->execute([$company]);
        }
        
        // Re-fetch companies
        $stmt->execute();
        $companies = $stmt->fetchAll();
    }

    echo json_encode([
        'success' => true,
        'companies' => $companies
    ]);

} catch (PDOException $e) {
    error_log("Database error in get_all_companies.php: " . $e->getMessage());
    
    // Return some default companies if database fails
    echo json_encode([
        'success' => true,
        'companies' => [
            ['company_name' => 'TCS'],
            ['company_name' => 'Infosys'],
            ['company_name' => 'Wipro'],
            ['company_name' => 'Accenture'],
            ['company_name' => 'Microsoft']
        ]
    ]);
    
} catch (Exception $e) {
    error_log("General error in get_all_companies.php: " . $e->getMessage());
    
    // Return default companies on any error
    echo json_encode([
        'success' => true,
        'companies' => [
            ['company_name' => 'TCS'],
            ['company_name' => 'Infosys'],
            ['company_name' => 'Wipro']
        ]
    ]);
}
?>