<?php
session_start();
header('Content-Type: application/json');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Change this to your MySQL username
define('DB_PASS', ''); // Change this to your MySQL password
define('DB_NAME', 'eee_placement'); // Updated database name

// Function to create database connection
function createConnection() {
    try {
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
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        return null;
    }
}

// Function to sanitize input
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Function to verify password (plain text comparison for your setup)
function verifyPassword($inputPassword, $storedPassword) {
    return $inputPassword === $storedPassword; // Direct comparison since passwords are stored in plain text
}

// Main login processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get and sanitize input data
        $username = sanitizeInput($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $userType = sanitizeInput($_POST['userType'] ?? '');
        
        // Validate input
        if (empty($username) || empty($password) || empty($userType)) {
            throw new Exception('All fields are required');
        }
        
        if (strlen($username) < 3) {
            throw new Exception('Username must be at least 3 characters long');
        }
        
        if (strlen($password) < 6) {
            throw new Exception('Password must be at least 6 characters long');
        }
        
        if (!in_array($userType, ['staff', 'student'])) {
            throw new Exception('Invalid user type');
        }
        
        // Create database connection
        $pdo = createConnection();
        if (!$pdo) {
            throw new Exception('Database connection failed');
        }
        
        // Map userType to database user_type(s)
        if ($userType === 'staff') {
            $allowedTypes = ['faculty'];
        } else {
            $allowedTypes = ['student', 'admin']; // Allow admin to login via student button
        }
        
        // Prepare SQL query for the user table
        $sql = "SELECT user_id, password, user_type 
                FROM user 
                WHERE user_id = :username AND user_type IN ('" . implode("','", $allowedTypes) . "')";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        
        $user = $stmt->fetch();
        
        if (!$user) {
            throw new Exception('Invalid username or password');
        }
        
        // Verify password (direct comparison)
        if (!verifyPassword($password, $user['password'])) {
            throw new Exception('Invalid username or password');
        }
        
        // Login successful - create session
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_type'] = $userType; // 'staff' or 'student' (button pressed)
        $_SESSION['db_user_type'] = $user['user_type']; // actual type from DB: 'faculty', 'student', or 'admin'
        $_SESSION['username'] = $user['user_id'];
        $_SESSION['login_time'] = time();
        
        // Log successful login (optional - you can create a simple log table later if needed)
        error_log("Successful login: " . $user['user_id'] . " (" . $user['user_type'] . ") at " . date('Y-m-d H:i:s'));
        
        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'Login successful! Redirecting...',
            'user_type' => $userType,
            'username' => $user['user_id'],
            'redirect' => 'dashboard.php'
        ]);
        
    } catch (Exception $e) {
        // Log failed login attempt
        error_log("Failed login attempt: " . ($username ?? 'Unknown') . " (" . ($userType ?? 'Unknown') . ") - " . $e->getMessage() . " at " . date('Y-m-d H:i:s'));
        
        // Return error response
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    // Invalid request method
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?>