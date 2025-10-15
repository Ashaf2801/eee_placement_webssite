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
    $userIds = $_POST['userId'] ?? [];
    $passwords = $_POST['userPassword'] ?? [];
    $userTypes = $_POST['userType'] ?? [];

    if (empty($userIds) || empty($passwords) || empty($userTypes)) {
        throw new Exception('All fields are required');
    }

    if (count($userIds) !== count($passwords) || count($userIds) !== count($userTypes)) {
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

    for ($i = 0; $i < count($userIds); $i++) {
        $userId = trim($userIds[$i]);
        $password = $passwords[$i];
        $userType = $userTypes[$i];

        // Validate
        if (empty($userId) || empty($password) || empty($userType)) {
            $failedUsers[] = "$userId (empty fields)";
            continue;
        }

        if (!in_array($userType, ['student', 'faculty', 'admin'])) {
            $failedUsers[] = "$userId (invalid type)";
            continue;
        }

        // Check if user already exists
        $checkSql = "SELECT user_id FROM user WHERE user_id = :user_id";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->bindParam(':user_id', $userId);
        $checkStmt->execute();

        if ($checkStmt->fetch()) {
            $failedUsers[] = "$userId (already exists)";
            continue;
        }

        // Insert new user
        try {
            $sql = "INSERT INTO user (user_id, password, user_type) VALUES (:user_id, :password, :user_type)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':user_type', $userType);
            $stmt->execute();
            $successCount++;
        } catch (Exception $e) {
            $failedUsers[] = "$userId (error: " . $e->getMessage() . ")";
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