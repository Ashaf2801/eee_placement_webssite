<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'eee_placement');

// Function to sanitize input
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

try {
    // Check if request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
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

    // Get and sanitize input data
    $registerNo = sanitizeInput($_POST['registerNo'] ?? '');
    $studentName = strtoupper(sanitizeInput($_POST['studentName'] ?? ''));
    $phoneNo = sanitizeInput($_POST['phoneNo'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $yearOfGraduation = intval($_POST['yearOfGraduation'] ?? 0);
    $companyName = sanitizeInput($_POST['companyName'] ?? '');
    $companyType = sanitizeInput($_POST['companyType'] ?? '');
    $package = floatval($_POST['package'] ?? 0);
    $isNewCompany = isset($_POST['isNewCompany']) && $_POST['isNewCompany'] === 'true';
    $editMode = isset($_POST['editMode']) && $_POST['editMode'] === 'true';
    $originalRegisterNo = sanitizeInput($_POST['originalRegisterNo'] ?? '');
    $originalCompanyName = sanitizeInput($_POST['originalCompanyName'] ?? '');

    // Handle new company input if needed
    if ($isNewCompany) {
        $companyName = sanitizeInput($_POST['companyName'] ?? ''); // This will be from the new company input field
        if (empty($companyName)) {
            throw new Exception('Company name is required');
        }
    }

    // Get round data
    $rounds = [];
    for ($i = 1; $i <= 6; $i++) {
        $roundKey = "round$i";
        $roundValue = $_POST[$roundKey] ?? '';
        if (!empty(trim($roundValue))) {
            // Check TEXT field limit (65535 characters)
            if (strlen($roundValue) > 65535) {
                throw new Exception("Round $i text exceeds maximum length of 65535 characters");
            }
            $rounds[$roundKey] = $roundValue;
        }
    }

    // Validation
    if (empty($registerNo) || empty($studentName) || empty($companyName) || 
        $yearOfGraduation <= 0 || empty($companyType) || $package <= 0) {
        throw new Exception('Please fill in all required fields');
    }

    // Validate register number format (flexible format like 22EE108, 21CS001, etc.)
    // if (!preg_match('/^[0-9]{2}[A-Z]{2}[0-9]{3}$/', $registerNo)) {
    //     throw new Exception('Invalid register number format. Expected format: 22EE108, 21CS001, etc.');
    // }

    // Validate email if provided
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    // Validate phone number if provided
    if (!empty($phoneNo) && !preg_match('/^[0-9]{10}$/', $phoneNo)) {
        throw new Exception('Phone number must be 10 digits');
    }

    // Handle new company addition
    if ($isNewCompany) {
        // Check if company already exists
        $checkCompanySQL = "SELECT company_name FROM company WHERE company_name = :company_name";
        $checkCompanyStmt = $pdo->prepare($checkCompanySQL);
        $checkCompanyStmt->bindParam(':company_name', $companyName);
        $checkCompanyStmt->execute();
        
        if (!$checkCompanyStmt->fetch()) {
            // Add new company
            $addCompanySQL = "INSERT INTO company (company_name) VALUES (:company_name)";
            $addCompanyStmt = $pdo->prepare($addCompanySQL);
            $addCompanyStmt->bindParam(':company_name', $companyName);
            $addCompanyStmt->execute();
        }
    }

    if ($editMode && !empty($originalRegisterNo) && !empty($originalCompanyName)) {
        // UPDATE MODE
        
        // Check if the student record exists
        $checkStudentSQL = "SELECT register_no FROM student WHERE register_no = :original_register_no";
        $checkStudentStmt = $pdo->prepare($checkStudentSQL);
        $checkStudentStmt->bindParam(':original_register_no', $originalRegisterNo);
        $checkStudentStmt->execute();
        
        if (!$checkStudentStmt->fetch()) {
            throw new Exception('Student record not found');
        }

        // Update student information
        $updateStudentSQL = "UPDATE student SET 
                             name = :name, 
                             phone_no = :phone_no, 
                             mail = :mail, 
                             year_of_graduation = :year_of_graduation 
                             WHERE register_no = :original_register_no";
        $updateStudentStmt = $pdo->prepare($updateStudentSQL);
        $updateStudentStmt->bindParam(':name', $studentName);
        $phoneNoValue = $phoneNo ?: null;
        $emailValue = $email ?: null;
        $updateStudentStmt->bindParam(':phone_no', $phoneNoValue);
        $updateStudentStmt->bindParam(':mail', $emailValue);
        $updateStudentStmt->bindParam(':year_of_graduation', $yearOfGraduation);
        $updateStudentStmt->bindParam(':original_register_no', $originalRegisterNo);
        $updateStudentStmt->execute();

        // If register number or company name changed, we need to handle the placement record carefully
        if ($registerNo !== $originalRegisterNo || $companyName !== $originalCompanyName) {
            // Delete old placement record
            $deletePlacementSQL = "DELETE FROM placement WHERE register_no = :original_register_no AND company_name = :original_company_name";
            $deletePlacementStmt = $pdo->prepare($deletePlacementSQL);
            $deletePlacementStmt->bindParam(':original_register_no', $originalRegisterNo);
            $deletePlacementStmt->bindParam(':original_company_name', $originalCompanyName);
            $deletePlacementStmt->execute();

            // Insert new placement record (will be handled by the INSERT section below)
            $editMode = false; // Treat as new record
        } else {
            // Update existing placement record
            $updatePlacementSQL = "UPDATE placement SET 
                                   company_type = :company_type,
                                   package = :package,
                                   round1 = :round1,
                                   round2 = :round2,
                                   round3 = :round3,
                                   round4 = :round4,
                                   round5 = :round5,
                                   round6 = :round6
                                   WHERE register_no = :register_no AND company_name = :company_name";
            
            $updatePlacementStmt = $pdo->prepare($updatePlacementSQL);
            $updatePlacementStmt->bindParam(':register_no', $originalRegisterNo);
            $updatePlacementStmt->bindParam(':company_name', $originalCompanyName);
            $updatePlacementStmt->bindParam(':company_type', $companyType);
            $updatePlacementStmt->bindParam(':package', $package);
            
            for ($i = 1; $i <= 6; $i++) {
                $roundKey = "round$i";
                $roundValue = $rounds[$roundKey] ?? null;
                $updatePlacementStmt->bindParam(":$roundKey", $roundValue);
            }
            
            $updatePlacementStmt->execute();
        }
    }

    if (!$editMode) {
        // INSERT MODE
        
        // Check if student already exists
        $checkStudentSQL = "SELECT register_no FROM student WHERE register_no = :register_no";
        $checkStudentStmt = $pdo->prepare($checkStudentSQL);
        $checkStudentStmt->bindParam(':register_no', $registerNo);
        $checkStudentStmt->execute();
        
        if ($checkStudentStmt->fetch()) {
            // Student exists, update their info
            $updateStudentSQL = "UPDATE student SET 
                                 name = :name, 
                                 phone_no = :phone_no, 
                                 mail = :mail, 
                                 year_of_graduation = :year_of_graduation 
                                 WHERE register_no = :register_no";
            $updateStudentStmt = $pdo->prepare($updateStudentSQL);
            $updateStudentStmt->bindParam(':name', $studentName);
            $phoneNoValue = $phoneNo ?: null;
            $emailValue = $email ?: null;
            $updateStudentStmt->bindParam(':phone_no', $phoneNoValue);
            $updateStudentStmt->bindParam(':mail', $emailValue);
            $updateStudentStmt->bindParam(':year_of_graduation', $yearOfGraduation);
            $updateStudentStmt->bindParam(':register_no', $registerNo);
            $updateStudentStmt->execute();
        } else {
            // Insert new student
            $insertStudentSQL = "INSERT INTO student (register_no, name, phone_no, mail, year_of_graduation) 
                                 VALUES (:register_no, :name, :phone_no, :mail, :year_of_graduation)";
            $insertStudentStmt = $pdo->prepare($insertStudentSQL);
            $insertStudentStmt->bindParam(':register_no', $registerNo);
            $insertStudentStmt->bindParam(':name', $studentName);
            $phoneNoValue = $phoneNo ?: null;
            $emailValue = $email ?: null;
            $insertStudentStmt->bindParam(':phone_no', $phoneNoValue);
            $insertStudentStmt->bindParam(':mail', $emailValue);
            $insertStudentStmt->bindParam(':year_of_graduation', $yearOfGraduation);
            $insertStudentStmt->execute();
        }

        // Check if placement record already exists
        $checkPlacementSQL = "SELECT register_no FROM placement WHERE register_no = :register_no AND company_name = :company_name";
        $checkPlacementStmt = $pdo->prepare($checkPlacementSQL);
        $checkPlacementStmt->bindParam(':register_no', $registerNo);
        $checkPlacementStmt->bindParam(':company_name', $companyName);
        $checkPlacementStmt->execute();
        
        if ($checkPlacementStmt->fetch()) {
            throw new Exception('Placement record for this student and company already exists. Use edit mode to update.');
        }

        // Insert new placement record
        $insertPlacementSQL = "INSERT INTO placement (register_no, company_name, company_type, package, round1, round2, round3, round4, round5, round6) 
                               VALUES (:register_no, :company_name, :company_type, :package, :round1, :round2, :round3, :round4, :round5, :round6)";
        
        $insertPlacementStmt = $pdo->prepare($insertPlacementSQL);
        $insertPlacementStmt->bindParam(':register_no', $registerNo);
        $insertPlacementStmt->bindParam(':company_name', $companyName);
        $insertPlacementStmt->bindParam(':company_type', $companyType);
        $insertPlacementStmt->bindParam(':package', $package);
        
        for ($i = 1; $i <= 6; $i++) {
            $roundKey = "round$i";
            $roundValue = $rounds[$roundKey] ?? null;
            $insertPlacementStmt->bindParam(":$roundKey", $roundValue);
        }
        
        $insertPlacementStmt->execute();
    }

    // Commit transaction
    $pdo->commit();

    // Return success response
    $message = $editMode ? 'Experience updated successfully!' : 'Experience added successfully!';
    echo json_encode([
        'success' => true,
        'message' => $message
    ]);

} catch (PDOException $e) {
    // Rollback transaction on database error
    if (isset($pdo)) {
        $pdo->rollback();
    }
    
    error_log("Database error in submit_experience.php: " . $e->getMessage());
    
    // Handle specific database errors
    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        if (strpos($e->getMessage(), 'phone_no') !== false) {
            $message = 'Phone number already exists for another student';
        } elseif (strpos($e->getMessage(), 'mail') !== false) {
            $message = 'Email already exists for another student';
        } else {
            $message = 'Duplicate entry detected';
        }
    } else {
        $message = 'Database error occurred';
    }
    
    echo json_encode([
        'success' => false,
        'message' => $message
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on general error
    if (isset($pdo)) {
        $pdo->rollback();
    }
    
    error_log("General error in submit_experience.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>