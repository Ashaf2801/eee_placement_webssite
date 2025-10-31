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
    $mailIds = $_POST['mailId'] ?? [];
    $userNames = $_POST['userName'] ?? [];
    $passwords = $_POST['userPassword'] ?? [];
    $userTypes = $_POST['userType'] ?? [];

    if (empty($mailIds) || empty($userNames) || empty($passwords) || empty($userTypes)) {
        throw new Exception('All fields are required');
    }

    if (count($mailIds) !== count($userNames) || count($mailIds) !== count($passwords) || count($mailIds) !== count($userTypes)) {
        throw new Exception('Invalid form data');
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

    $pdo->beginTransaction();

    $successCount = 0;
    $failedUsers = [];

    for ($i = 0; $i < count($mailIds); $i++) {
        $mailId = trim($mailIds[$i]);
        $userName = trim($userNames[$i]);
        $password = $passwords[$i];
        $userType = $userTypes[$i];

        // Validate
        if (empty($mailId) || empty($userName) || empty($password) || empty($userType)) {
            $failedUsers[] = "$mailId (empty fields)";
            continue;
        }

        if (!in_array($userType, ['student', 'faculty', 'admin'])) {
            $failedUsers[] = "$mailId (invalid type)";
            continue;
        }

        // Check if user already exists
        $checkSql = "SELECT mail_id FROM users WHERE mail_id = :mail_id";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->bindParam(':mail_id', $mailId);
        $checkStmt->execute();

        if ($checkStmt->fetch()) {
            $failedUsers[] = "$mailId (already exists)";
            continue;
        }

        // Insert new user
        try {
            $sql = "INSERT INTO users (mail_id, user_name, password, user_type) VALUES (:mail_id, :user_name, :password, :user_type)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':mail_id', $mailId);
            $stmt->bindParam(':user_name', $userName);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':user_type', $userType);
            $stmt->execute();
            $successCount++;
        } catch (Exception $e) {
            $failedUsers[] = "$mailId (error: " . $e->getMessage() . ")";
        }
    }

    $pdo->commit();

    $message = "$successCount user(s) added successfully";
    if (!empty($failedUsers)) {
        $message .= ". Failed: " . implode(', ', $failedUsers);
    }

    echo json_encode([
        'success' => $successCount > 0,
        'message' => $message,
        'success_count' => $successCount,
        'failed_count' => count($failedUsers)
    ]);

} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollback();
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>