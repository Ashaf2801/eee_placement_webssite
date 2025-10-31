<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Start session to check permissions
session_start();

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'eee_placement');

try {
    // Check if user is logged in and has permission
    if (!isset($_SESSION['mail_id']) || !isset($_SESSION['user_type'])) {
        throw new Exception('Unauthorized access. Please log in.');
    }

    $currentUserType = $_SESSION['user_type'];

    // Only admin and faculty can delete
    if (!in_array($currentUserType, ['admin', 'faculty'])) {
        throw new Exception('You do not have permission to delete experiences. Only admin and faculty can delete.');
    }

    // Get POST parameters
    $registerNo = $_POST['register_no'] ?? null;
    $companyName = $_POST['company_name'] ?? null;
    
    if (!$registerNo || !$companyName) {
        throw new Exception('Register number and company name are required');
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

    // Start transaction
    $pdo->beginTransaction();

    try {
        // First check if the record exists
        $checkSql = "SELECT register_no, company_name FROM placement 
                     WHERE register_no = :register_no AND company_name = :company_name";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->bindParam(':register_no', $registerNo, PDO::PARAM_STR);
        $checkStmt->bindParam(':company_name', $companyName, PDO::PARAM_STR);
        $checkStmt->execute();
        $existingRecord = $checkStmt->fetch();

        if (!$existingRecord) {
            throw new Exception('No placement record found for this student and company');
        }

        // Delete from placement table
        $deleteSql = "DELETE FROM placement 
                      WHERE register_no = :register_no AND company_name = :company_name";
        $deleteStmt = $pdo->prepare($deleteSql);
        $deleteStmt->bindParam(':register_no', $registerNo, PDO::PARAM_STR);
        $deleteStmt->bindParam(':company_name', $companyName, PDO::PARAM_STR);
        $deleteStmt->execute();

        $deletedRows = $deleteStmt->rowCount();

        if ($deletedRows === 0) {
            throw new Exception('Failed to delete the placement record');
        }

        // Commit transaction
        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Placement experience deleted successfully',
            'deleted_rows' => $deletedRows
        ]);

    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        throw $e;
    }

} catch (PDOException $e) {
    error_log("Database error in delete_experience.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("General error in delete_experience.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>