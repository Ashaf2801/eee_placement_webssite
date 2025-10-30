<?php
// Enable detailed error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

header('Content-Type: application/json');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');                 // Change if different
define('DB_PASS', '');                     // Change if different
define('DB_NAME', 'eee_placement');     // Change if different

// Create database connection for MySQL
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        return null;
    }
}

// Hardcoded OTPs for password reset
$HARDCODED_OTPS = [
    "144676", "234567", "345678", "456789", "567890", "678901", "789012", "890123", "901234", "012345",
    "111111", "222222", "333333", "444444", "555555", "666666", "777777", "888888", "999444", "963214",
    "121212", "232323", "343434", "454545", "565656", "676767", "787878", "898989", "909090", "010101"
];

// Simple email function
function sendOTPEmail($to, $userName, $otp) {
    $subject = "OTP to change the password of EEE placement web portal";
    
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
            .otp-display { font-size: 32px; font-weight: bold; color: #667eea; text-align: center; padding: 20px; background: white; border-radius: 8px; margin: 20px 0; letter-spacing: 8px; }
            .footer { text-align: center; color: #666; font-size: 12px; margin-top: 20px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>EEE Placement Portal - Password Reset</h2>
            </div>
            <div class='content'>
                <p>Dear <strong>$userName</strong>,</p>
                <p>You have requested to reset your password for the EEE Placement System.</p>
                <p><strong>This is the OTP to change your password:</strong></p>
                <div class='otp-display'>$otp</div>
                <p><strong>Important:</strong> This OTP is valid for 10 minutes only.</p>
                <p>If you did not request this password reset, please ignore this email and your password will remain unchanged.</p>
            </div>
            <div class='footer'>
                <p>This is an automated email from EEE Placement System. Please do not reply to this email.</p>
            </div>
        </div>
    </body>
    </html>";
    
    $headers = "From: EEE Placement Portal <noreply@eeeplacement.com>\r\n";
    $headers .= "Reply-To: noreply@eeeplacement.com\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    // Try to send the email
    $mailSent = mail($to, $subject, $message, $headers);
    
    if ($mailSent) {
        error_log("OTP email sent successfully to: $to | OTP: $otp");
        return true;
    } else {
        error_log("Failed to send OTP email to: $to");
        return false;
    }
}

function generateRandomOTP($otpList) {
    return $otpList[array_rand($otpList)];
}

// Main request handling
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$action = $_POST['action'] ?? 'login';
error_log("Received action: $action");

try {
    $db = getDBConnection();
    if (!$db) {
        throw new Exception('Could not connect to database. Please check your database configuration.');
    }
    
    switch ($action) {
        case 'login':
            handleLogin($db);
            break;
            
        case 'request_otp':
            handleOTPRequest($db);
            break;
            
        case 'verify_otp':
            verifyOTP($db);
            break;
            
        case 'reset_password':
            resetPassword($db);
            break;
            
        default:
            throw new Exception('Invalid action: ' . $action);
    }
    
} catch (Exception $e) {
    error_log("Error in $action: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function handleLogin($db) {
    $email = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    error_log("Login attempt for: $email");
    
    if (empty($email) || empty($password)) {
        throw new Exception('Email and password are required');
    }
    
    // Get user from database
    $stmt = $db->prepare("SELECT mail_id, user_name, password, user_type FROM users WHERE mail_id = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        error_log("User not found: $email");
        throw new Exception('Invalid email or password');
    }
    
    error_log("User found: " . $user['user_name'] . " | DB Password: " . $user['password']);
    
    // Check password - direct comparison for plain text
    if ($password !== $user['password']) {
        error_log("Password mismatch for: $email");
        throw new Exception('Invalid email or password');
    }
    
    // Create session - USE CONSISTENT VARIABLE NAMES
    $_SESSION['mail_id'] = $user['mail_id'];           // Changed from user_id
    $_SESSION['user_name'] = $user['user_name'];
    $_SESSION['user_type'] = $user['user_type'];
    $_SESSION['login_time'] = time();
    
    // Determine redirect based on user type
    $redirect = getRedirectUrl($user['user_type']);
    
    error_log("Login successful for: $email, redirecting to: $redirect");
    error_log("Session set - mail_id: " . $_SESSION['mail_id'] . ", user_name: " . $_SESSION['user_name'] . ", user_type: " . $_SESSION['user_type']);
    
    echo json_encode([
        'success' => true,
        'message' => 'Login successful!',
        'redirect' => $redirect,
        'user_type' => $user['user_type'],
        'user_name' => $user['user_name']
    ]);
}

function handleOTPRequest($db) {
    global $HARDCODED_OTPS;
    
    $email = $_POST['email'] ?? '';
    error_log("OTP request for: $email");
    
    if (empty($email)) {
        throw new Exception('Email is required');
    }
    
    // Check if user exists in database
    $stmt = $db->prepare("SELECT mail_id, user_name FROM users WHERE mail_id = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        // For security, don't reveal if email exists
        error_log("Password reset requested for non-existent email: $email");
        echo json_encode([
            'success' => true,
            'message' => 'If this email exists in our system, an OTP has been sent'
        ]);
        return;
    }
    
    error_log("User found for OTP: " . $user['user_name']);
    
    // Generate random OTP from hardcoded list
    $otp = generateRandomOTP($HARDCODED_OTPS);
    
    // Store in session
    $_SESSION['reset_otp'] = $otp;
    $_SESSION['reset_email'] = $email;
    $_SESSION['otp_expiry'] = time() + 600; // 10 minutes
    
    error_log("Generated OTP: $otp for email: $email");
    
    // Send email using the improved function
    $emailSent = sendOTPEmail($email, $user['user_name'], $otp);
    
    if ($emailSent) {
        echo json_encode([
            'success' => true,
            'message' => 'OTP sent to your email! Please check your inbox.',
            'debug_otp' => $otp // This will show OTP on frontend for testing
        ]);
    } else {
        // Even if email fails, allow the process to continue for testing
        echo json_encode([
            'success' => true,
            'message' => 'OTP generated. Check server logs for OTP value.',
            'debug_otp' => $otp
        ]);
    }
}

function verifyOTP($db) {
    global $HARDCODED_OTPS;
    
    $enteredOTP = $_POST['otp'] ?? '';
    error_log("OTP verification attempt: $enteredOTP");
    
    if (empty($enteredOTP)) {
        throw new Exception('OTP is required');
    }
    
    // Check if OTP is in the hardcoded list
    if (!in_array($enteredOTP, $HARDCODED_OTPS)) {
        throw new Exception('Invalid OTP. Please enter a valid OTP.');
    }
    
    // Check if OTP was requested and not expired
    if (!isset($_SESSION['reset_otp']) || !isset($_SESSION['otp_expiry'])) {
        throw new Exception('Please request an OTP first');
    }
    
    // Check expiry
    if (time() > $_SESSION['otp_expiry']) {
        unset($_SESSION['reset_otp'], $_SESSION['reset_email'], $_SESSION['otp_expiry']);
        throw new Exception('OTP has expired. Please request a new one.');
    }
    
    // Verify that the entered OTP matches the one we sent
    if ($enteredOTP !== $_SESSION['reset_otp']) {
        throw new Exception('Invalid OTP. Please try again.');
    }
    
    // Mark as verified
    $_SESSION['otp_verified'] = true;
    
    echo json_encode([
        'success' => true,
        'message' => 'OTP verified successfully! You can now reset your password.'
    ]);
}

function resetPassword($db) {
    if (!isset($_SESSION['otp_verified']) || !$_SESSION['otp_verified']) {
        throw new Exception('Please verify OTP first');
    }
    
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $email = $_SESSION['reset_email'] ?? '';
    
    error_log("Password reset attempt for: $email");
    
    if (empty($newPassword) || empty($confirmPassword)) {
        throw new Exception('Both password fields are required');
    }
    
    if ($newPassword !== $confirmPassword) {
        throw new Exception('Passwords do not match');
    }
    
    if (strlen($newPassword) < 6) {
        throw new Exception('Password must be at least 6 characters');
    }
    
    // Update password in database (store as plain text for simplicity)
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE mail_id = ?");
    $stmt->execute([$newPassword, $email]);
    
    if ($stmt->rowCount() > 0) {
        // Clear reset session
        unset($_SESSION['reset_otp'], $_SESSION['reset_email'], $_SESSION['otp_expiry'], $_SESSION['otp_verified']);
        
        error_log("Password reset successful for: $email");
        
        echo json_encode([
            'success' => true,
            'message' => 'Password reset successfully! You can now login with your new password.'
        ]);
    } else {
        throw new Exception('Failed to reset password. Please try again.');
    }
}

function getRedirectUrl($userType) {
    switch ($userType) {
        case 'admin':
            return 'dashboard.php';
        case 'faculty':
            return 'dashboard.php';
        case 'student':
        default:
            return 'dashboard.php';
    }
}
?>