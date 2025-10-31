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
    $year = intval($_POST['batchYear'] ?? 0);

    if ($year < 2020 || $year > 2030) {
        throw new Exception('Invalid year. Please enter a year between 2020 and 2030');
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

    // Check if batch already exists
    $checkSql = "SELECT year FROM placement_sheets WHERE year = :year";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->bindParam(':year', $year);
    $checkStmt->execute();

    if ($checkStmt->fetch()) {
        throw new Exception('Batch sheet for year ' . $year . ' already exists');
    }

    // For demonstration, we'll store a Google Sheets template URL
    // In production, you would use Google Sheets API to create the sheet programmatically
    
    // Template sheet URL - Replace this with your actual template or API creation logic
    $sheetUrl = "https://docs.google.com/spreadsheets/d/1eN2gboiDR6cUAhw10xUQS_MapLcZddGjondkRn2Mqxo/edit#gid=0";
    
    // Note: To actually create sheets programmatically, you need to:
    // 1. Set up Google Sheets API credentials
    // 2. Use Google API PHP Client library
    // 3. Create sheet with proper naming: EEE_placement_details_sheet_[YEAR]
    // 4. Share with 22ee108@psgitech.ac.in
    // 5. Store the created sheet URL
    
    // Insert batch record
    $sql = "INSERT INTO placement_sheets (year, sheet_url) VALUES (:year, :sheet_url)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':year', $year);
    $stmt->bindParam(':sheet_url', $sheetUrl);
    $stmt->execute();

    echo json_encode([
        'success' => true,
        'message' => 'Batch sheet created successfully for year ' . $year,
        'year' => $year,
        'sheet_url' => $sheetUrl
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>