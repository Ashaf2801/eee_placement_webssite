<?php
session_start();

// Database Configuration - UPDATE WITH YOUR MYSQL CREDENTIALS
define('DB_HOST', 'localhost');
define('DB_USER', 'root');                 // Change to your MySQL username
define('DB_PASS', '');                     // Change to your MySQL password
define('DB_NAME', 'eee_placement');     // Change to your database name
define('DB_CHARSET', 'utf8mb4');

// Create database connection for MySQL
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        throw new Exception('Database connection error. Please check your credentials.');
    }
}

// Hardcoded OTPs for password reset
$HARDCODED_OTPS = [
    "123456", "234567", "345678", "456789", "567890", "678901", "789012", "890123", "901234", "012345",
    "111111", "222222", "333333", "444444", "555555", "666666", "777777", "888888", "999999", "000000",
    "121212", "232323", "343434", "454545", "565656", "676767", "787878", "898989", "909090", "010101"
];

// Improved email function that actually sends emails
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
?>