<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header('Location: index.html');
    exit();
}

$currentUserMail = $_SESSION['mail_id'];
$currentUserType = $_SESSION['user_type'];
$username = $_SESSION['user_name'] ?? 'User';
$firstLetter = strtoupper(substr($username, 0, 1));

$currentYear = date('Y');
$canEdit = in_array($currentUserType, ['admin', 'faculty']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Placement Experience - EEE Department</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }

        .navbar {
            background-color: #2c3e50;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .logo-container {
            display: flex;
            align-items: center;
        }

        .logo {
            height: 50px;
            margin-right: 15px;
        }

        .nav-content {
            display: flex;
            align-items: center;
            gap: 30px;
        }

        .nav-title {
            font-size: 22px;
            font-weight: bold;
        }

        .nav-links {
            display: flex;
            gap: 15px;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 6px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .nav-links a:hover,
        .nav-links a:focus {
            background-color: #34495e;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            outline: none;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 16px;
            background: #2c3e50;
            border-radius: 8px;
        }

        .user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 16px;
            color: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .user-info span {
            font-weight: 600;
            font-size: 14px;
        }

        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
            padding: 8px;
            border-radius: 4px;
        }

        .mobile-menu {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 999;
        }

        .mobile-menu-content {
            position: fixed;
            top: 0;
            right: -400px;
            width: 350px;
            height: 100%;
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            transition: right 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            overflow-y: auto;
            box-shadow: -5px 0 30px rgba(0, 0, 0, 0.3);
        }

        .mobile-menu.active {
            display: block;
        }

        .mobile-menu.active .mobile-menu-content {
            right: 0;
        }

        .mobile-nav-links a:hover::before {
            left: 100%;
        }

        .mobile-nav-links a:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(8px);
            border-color: rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        .mobile-menu-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background: rgba(255, 255, 255, 0.05);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .mobile-menu-close {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: white;
            font-size: 22px;
            cursor: pointer;
            padding: 8px;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .mobile-logout-link:hover::before {
            left: 100%;
        }

        .mobile-logout-link:hover {
            background: linear-gradient(135deg, #006effff 0%, #0055ccff 100%) !important;
            transform: translateX(8px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        .mobile-nav-links {
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding: 20px 25px;
        }

        .mobile-nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 16px 20px;
            border-radius: 12px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 15px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .mobile-user-info {
            margin: 20px 25px;
        }

        .mobile-logout-link {
            background: linear-gradient(135deg, #0080ffff 0%, #006effff 100%) !important;
            margin-top: 10px;
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .page-header {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            text-align: center;
        }

        .page-header h1 {
            color: #2c3e50;
            font-size: 32px;
            margin-bottom: 10px;
        }

        .batch-container {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .batch-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .batch-card {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            font-size: 18px;
            font-weight: 600;
        }

        .company-table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            display: none;
            margin-bottom: 30px;
        }

        .company-table-container.active {
            display: block;
        }

        .table-header {
            background: #34495e;
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .back-btn {
            background: #3498db;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .company-name {
            color: #3498db;
            cursor: pointer;
            font-weight: 600;
        }

        .student-table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            display: none;
            margin-bottom: 30px;
        }

        .student-table-container.active {
            display: block;
        }

        .experience-link {
            background: #e74c3c;
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            border: none;
        }

        .delete-btn {
            background: #e74c3c;
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            border: none;
            margin-left: 5px;
        }

        .confirm-modal {
            display: none;
            position: fixed;
            z-index: 1001;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .confirm-modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 30px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        .confirm-modal-content h3 {
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .confirm-modal-actions {
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .confirm-btn-cancel,
        .confirm-btn-delete {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .confirm-btn-cancel {
            background: #95a5a6;
            color: white;
        }

        .confirm-btn-delete {
            background: #e74c3c;
            color: white;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 8px;
            width: 80%;
            max-width: 800px;
            max-height: 80vh;
            overflow-y: auto;
            position: relative;
        }

        .modal-close {
            position: absolute;
            right: 20px;
            top: 15px;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: #aaa;
        }

        .modal-header {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #ecf0f1;
        }

        .modal-header h2 {
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .round-section {
            margin-bottom: 25px;
            padding: 20px;
            border: 1px solid #ecf0f1;
            border-radius: 6px;
            background: #f8f9fa;
        }

        .round-title {
            color: #2c3e50;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .round-content {
            color: #34495e;
            line-height: 1.6;
            white-space: pre-wrap;
        }

        .add-experience-container {
            text-align: center;
            margin-bottom: 30px;
        }

        .add-experience-btn {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 18px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
        }

        .add-experience-modal {
            display: none;
            position: fixed;
            z-index: 1001;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            overflow-y: auto;
        }

        .add-experience-modal-content {
            background-color: white;
            margin: 2% auto;
            padding: 30px;
            border-radius: 8px;
            width: 90%;
            max-width: 900px;
            position: relative;
            max-height: 90vh;
            overflow-y: auto;
        }

        .add-experience-modal-close {
            position: absolute;
            right: 20px;
            top: 15px;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: #aaa;
        }

        .form-header {
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #ecf0f1;
        }

        .form-header h2 {
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: 600;
            margin-bottom: 5px;
            color: #2c3e50;
        }

        .form-group input, 
        .form-group select, 
        .form-group textarea {
            padding: 10px 12px;
            border: 2px solid #ecf0f1;
            border-radius: 6px;
            font-size: 14px;
            font-family: inherit;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 150px;
        }

        .add-company-option {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            margin-top: 5px;
        }

        .new-company-input {
            display: none;
            margin-top: 10px;
        }

        .new-company-input.active {
            display: block;
        }

        .char-count {
            font-size: 12px;
            margin-top: 5px;
            text-align: right;
            color: #7f8c8d;
        }

        .char-count.warning {
            color: #e67e22;
        }

        .char-count.danger {
            color: #e74c3c;
            font-weight: bold;
        }

        .rounds-section {
            margin-top: 30px;
        }

        .rounds-section h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ecf0f1;
        }

        .round-group {
            margin-bottom: 25px;
        }

        .round-label {
            font-weight: 600;
            margin-bottom: 8px;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            background: #f4f6fa;
            border-radius: 8px;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ecf0f1;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-cancel {
            background: #95a5a6;
            color: white;
        }

        .btn-submit {
            background: #27ae60;
            color: white;
        }

        .message {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            display: none;
        }

        .message.success {
            background: #d5edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .message.active {
            display: block;
        }

        .edit-mode-header {
            background: #f39c12;
            color: white;
            padding: 10px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
        }

        .loading {
            text-align: center;
            padding: 20px;
            color: #7f8c8d;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
        }

        @media (max-width: 1200px) {
            .nav-links {
                display: none;
            }
            
            .user-info {
                display: none;
            }
            
            .mobile-menu-btn {
                display: block;
            }
        }

        @media (max-width: 768px) {
            .navbar {
                padding: 15px 20px;
            }
            
            .container {
                padding: 0 15px;
            }
            
            .batch-grid {
                grid-template-columns: 1fr;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo-container">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/4/43/Itechlogo.png/738px-Itechlogo.png" alt="PSG iTech Logo" class="logo">
            <span class="nav-title">EEE Department</span>
        </div>
        
        <div class="nav-content">
            <div class="nav-links">
                <a href="dashboard.php"><i class="fas fa-home"></i> HOME</a>
                <a href="placement_experience.php" style="background: #34495e;"><i class="fas fa-book"></i> PLACED EXPERIENCE</a>
                <a href="chatbot.php"><i class="fas fa-pencil-alt"></i> PREP WITH AI</a>
                <?php if (in_array($currentUserType, ['admin', 'faculty'])): ?>
                    <a href="admin_panel.php"><i class="fas fa-user-shield"></i> Admin Panel</a>
                <?php endif; ?>
                <a href="logout.php" style="background: #009dffff;"><i class="fas fa-sign-out-alt"></i> LOGOUT</a>
            </div>
            
            <div class="user-info">
                <div class="user-avatar"><?php echo $firstLetter; ?></div>
                <span><?php echo htmlspecialchars($username); ?></span>
                <small style="font-size: 11px; opacity: 0.8;">(<?php echo ucfirst($currentUserType); ?>)</small>
            </div>
        </div>
        
        <button class="mobile-menu-btn" id="mobileMenuBtn">
            <i class="fas fa-bars"></i>
        </button>
    </nav>

    <div class="mobile-menu" id="mobileMenu">
        <div class="mobile-menu-content">
            <div class="mobile-menu-header">
                <div class="mobile-user-info">
                    <div>
                        <div style="font-weight: 700; font-size: 16px; color: white;"><?php echo htmlspecialchars($username); ?>
                            <div style="font-size: 13px; color: #bdc3c7; background: rgba(255, 255, 255, 0.1); padding: 1px 10px; border-radius: 20px; display: inline-block;"><?php echo ucfirst($currentUserType); ?></div>
                        </div>
                    </div>
                </div>
                <button class="mobile-menu-close" id="mobileMenuClose">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="mobile-nav-links">
                <a href="dashboard.php"><i class="fas fa-home"></i><span>HOME</span></a>
                <a href="placement_experience.php"><i class="fas fa-book"></i><span>PLACED EXPERIENCE</span></a>
                <a href="chatbot.php"><i class="fas fa-pencil-alt"></i><span>PREP WITH AI</span></a>
                <?php if (in_array($currentUserType, ['admin', 'faculty'])): ?>
                    <a href="admin_panel.php"><i class="fas fa-user-shield"></i><span>Admin Panel</span></a>
                <?php endif; ?>
                <a href="logout.php" class="mobile-logout-link"><i class="fas fa-sign-out-alt"></i><span>LOGOUT</span></a>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-trophy"></i> Placement Experience</h1>
            <p>Explore placement experiences of our students across different batches</p>
        </div>

        <?php if ($canEdit): ?>
        <div class="add-experience-container">
            <button class="add-experience-btn" onclick="openAddExperienceModal()">
                <i class="fas fa-plus"></i> Add Your Experience
            </button>
        </div>
        <?php endif; ?>

        <div class="batch-container" id="batchContainer">
            <h2><i class="fas fa-graduation-cap"></i> Select Graduation Year</h2>
            <div class="batch-grid" id="batchGrid"></div>
        </div>

        <div class="company-table-container" id="companyTableContainer">
            <div class="table-header">
                <h2 id="companyTableTitle">Companies - Batch 2024</h2>
                <button class="back-btn" onclick="showBatchSelection()">
                    <i class="fas fa-arrow-left"></i> Back to Batches
                </button>
            </div>
            <div id="companyTableContent"></div>
        </div>

        <div class="student-table-container" id="studentTableContainer">
            <div class="table-header">
                <h2 id="studentTableTitle">Students - Company Name</h2>
                <button class="back-btn" onclick="showCompanyTable()">
                    <i class="fas fa-arrow-left"></i> Back to Companies
                </button>
            </div>
            <div id="studentTableContent"></div>
        </div>
    </div>

    <div id="experienceModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal()">&times;</span>
            <div class="modal-header">
                <h2 id="modalStudentName">Student Name</h2>
                <p id="modalCompanyName">Company Name</p>
            </div>
            <div id="modalContent"></div>
        </div>
    </div>

    <div id="confirmModal" class="confirm-modal">
        <div class="confirm-modal-content">
            <h3><i class="fas fa-exclamation-triangle" style="color: #e74c3c;"></i> Confirm Delete</h3>
            <p id="confirmMessage">Are you sure you want to delete this experience?</p>
            <div class="confirm-modal-actions">
                <button class="confirm-btn-cancel" onclick="closeConfirmModal()">Cancel</button>
                <button class="confirm-btn-delete" onclick="confirmDelete()">Delete</button>
            </div>
        </div>
    </div>

    <div id="addExperienceModal" class="add-experience-modal">
        <div class="add-experience-modal-content">
            <span class="add-experience-modal-close" onclick="closeAddExperienceModal()">&times;</span>
            
            <div class="edit-mode-header" id="editModeHeader" style="display: none;">
                <i class="fas fa-edit"></i> Editing Experience - You can update your existing placement details
            </div>
            
            <div class="form-header">
                <h2 id="formTitle">Add Your Placement Experience</h2>
                <p>Share your placement journey to help future students</p>
            </div>

            <div class="message" id="formMessage"></div>

            <form id="addExperienceForm">
                <input type="hidden" id="editMode" value="false">
                <input type="hidden" id="originalRegisterNo" value="">
                <input type="hidden" id="originalCompanyName" value="">

                <div class="form-row">
                    <div class="form-group">
                        <label for="registerNo">Register Number *</label>
                        <input type="text" id="registerNo" name="registerNo" required placeholder="e.g., 715522105008">
                    </div>
                    <div class="form-group">
                        <label for="studentName">Name *</label>
                        <input type="text" id="studentName" name="studentName" required placeholder="Your full name">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="phoneNo">Phone Number</label>
                        <input type="tel" id="phoneNo" name="phoneNo" placeholder="Your phone number">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="Your email address">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="yearOfGraduation">Year of Graduation *</label>
                        <input list="yearList" id="yearOfGraduation" name="yearOfGraduation" required placeholder="Search or select year...">
                        <datalist id="yearList">
                            <?php
                                for ($year = 2024; $year <= 2100; $year++) {
                                    echo "<option value=\"$year\">";
                                }
                            ?>
                        </datalist>
                    </div>
                    <div class="form-group">
                        <label for="package">Package (LPA) *</label>
                        <input type="number" id="package" name="package" step="0.01" min="0" required placeholder="e.g., 7.5">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="companyName">Company Name *</label>
                        <select id="companyName" name="companyName" required>
                            <option value="">Loading companies...</option>
                        </select>
                        <button type="button" class="add-company-option" onclick="toggleNewCompanyInput()">
                            + Add New Company
                        </button>
                        <div class="new-company-input" id="newCompanyInput">
                            <input type="text" id="newCompanyName" placeholder="Enter new company name">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="companyType">Role/Company Type *</label>
                        <select id="companyType" name="companyType" required>
                            <option value="">Select Type</option>
                            <option value="IT - Service">IT - Service</option>
                            <option value="IT - Product">IT - Product</option>
                            <option value="Core">Core</option>
                            <option value="Consulting">Consulting</option>
                            <option value="Managerial">Managerial</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="rounds-section">
                    <h3><i class="fas fa-clipboard-list"></i> Interview Rounds Experience (Leave empty if not applicable)</h3>
                    
                    <div class="round-group">
                        <div class="round-label">
                            <i class="fas fa-circle"></i>
                            <span>Round 1</span>
                        </div>
                        <textarea id="round1" name="round1" placeholder="Describe your experience in Round 1"></textarea>
                        <div class="char-count" id="round1Count">0 / 65535 characters</div>
                    </div>

                    <div class="round-group">
                        <div class="round-label">
                            <i class="fas fa-circle"></i>
                            <span>Round 2</span>
                        </div>
                        <textarea id="round2" name="round2" placeholder="Describe your experience in Round 2"></textarea>
                        <div class="char-count" id="round2Count">0 / 65535 characters</div>
                    </div>

                    <div class="round-group">
                        <div class="round-label">
                            <i class="fas fa-circle"></i>
                            <span>Round 3</span>
                        </div>
                        <textarea id="round3" name="round3" placeholder="Describe your experience in Round 3"></textarea>
                        <div class="char-count" id="round3Count">0 / 65535 characters</div>
                    </div>

                    <div class="round-group">
                        <div class="round-label">
                            <i class="fas fa-circle"></i>
                            <span>Round 4</span>
                        </div>
                        <textarea id="round4" name="round4" placeholder="Describe your experience in Round 4"></textarea>
                        <div class="char-count" id="round4Count">0 / 65535 characters</div>
                    </div>

                    <div class="round-group">
                        <div class="round-label">
                            <i class="fas fa-circle"></i>
                            <span>Round 5</span>
                        </div>
                        <textarea id="round5" name="round5" placeholder="Describe your experience in Round 5"></textarea>
                        <div class="char-count" id="round5Count">0 / 65535 characters</div>
                    </div>

                    <div class="round-group">
                        <div class="round-label">
                            <i class="fas fa-circle"></i>
                            <span>Round 6</span>
                        </div>
                        <textarea id="round6" name="round6" placeholder="Describe your experience in Round 6"></textarea>
                        <div class="char-count" id="round6Count">0 / 65535 characters</div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-cancel" onclick="closeAddExperienceModal()">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-submit" id="submitBtn">
                        <i class="fas fa-save"></i> Submit Experience
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const mobileMenu = document.getElementById('mobileMenu');
            const mobileMenuClose = document.getElementById('mobileMenuClose');

            if (mobileMenuBtn) {
                mobileMenuBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    mobileMenu.classList.add('active');
                    document.body.style.overflow = 'hidden';
                });
            }

            if (mobileMenuClose) {
                mobileMenuClose.addEventListener('click', function(e) {
                    e.stopPropagation();
                    mobileMenu.classList.remove('active');
                    document.body.style.overflow = '';
                });
            }

            if (mobileMenu) {
                mobileMenu.addEventListener('click', function(e) {
                    if (e.target === mobileMenu) {
                        mobileMenu.classList.remove('active');
                        document.body.style.overflow = '';
                    }
                });
            }

            const mobileMenuLinks = document.querySelectorAll('.mobile-nav-links a');
            mobileMenuLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    mobileMenu.classList.remove('active');
                    document.body.style.overflow = '';
                });
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && mobileMenu.classList.contains('active')) {
                    mobileMenu.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });

            loadBatches();
            loadCompaniesForDropdown();
            initializeCharacterCounters();
            initializeFormValidation();
        });

        let currentBatch = null;
        let currentCompany = null;
        let companies = [];
        let canEdit = <?php echo json_encode($canEdit); ?>;
        let deleteRegisterNo = null;
        let deleteCompanyName = null;

        function loadCompaniesForDropdown() {
            const companySelect = document.getElementById('companyName');
            companySelect.innerHTML = '<option value="">Loading companies...</option>';
            
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 5000);
            
            fetch('get_all_companies.php', { signal: controller.signal })
            .then(response => {
                clearTimeout(timeoutId);
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                companies = data.companies || [];
                companySelect.innerHTML = '<option value="">Select Company</option>';
                
                if (companies.length === 0) {
                    const defaultCompanies = ['TCS', 'Infosys', 'Wipro', 'Accenture', 'Microsoft', 'Google', 'Amazon'];
                    defaultCompanies.forEach(companyName => {
                        const option = document.createElement('option');
                        option.value = companyName;
                        option.textContent = companyName;
                        companySelect.appendChild(option);
                    });
                } else {
                    companies.forEach(company => {
                        const option = document.createElement('option');
                        option.value = company.company_name;
                        option.textContent = company.company_name;
                        companySelect.appendChild(option);
                    });
                }
            })
            .catch(error => {
                clearTimeout(timeoutId);
                console.error('Error loading companies:', error);
                companySelect.innerHTML = '<option value="">Select Company</option>';
                const defaultCompanies = ['TCS', 'Infosys', 'Wipro', 'Accenture', 'Microsoft', 'Google', 'Amazon'];
                defaultCompanies.forEach(companyName => {
                    const option = document.createElement('option');
                    option.value = companyName;
                    option.textContent = companyName;
                    companySelect.appendChild(option);
                });
            });
        }

        function initializeCharacterCounters() {
            const textAreas = ['round1', 'round2', 'round3', 'round4', 'round5', 'round6'];
            textAreas.forEach(roundId => {
                const textArea = document.getElementById(roundId);
                textArea.addEventListener('input', function() {
                    updateCharacterCount(roundId);
                });
            });
        }

        function updateCharacterCount(roundId) {
            const textArea = document.getElementById(roundId);
            const counter = document.getElementById(roundId + 'Count');
            const maxLength = 65535;
            const currentLength = textArea.value.length;
            
            counter.textContent = `${currentLength} / ${maxLength} characters`;
            counter.classList.remove('warning', 'danger');
            
            if (currentLength > maxLength * 0.9) {
                counter.classList.add('danger');
            } else if (currentLength > maxLength * 0.8) {
                counter.classList.add('warning');
            }
            
            if (currentLength >= maxLength) {
                textArea.value = textArea.value.substring(0, maxLength);
                counter.textContent = `${maxLength} / ${maxLength} characters (Limit reached!)`;
                counter.classList.add('danger');
            }
        }

        function initializeFormValidation() {
            const studentNameInput = document.getElementById('studentName');
            studentNameInput.addEventListener('input', function() {
                this.value = this.value.toUpperCase();
            });

            const form = document.getElementById('addExperienceForm');
            form.addEventListener('submit', handleFormSubmit);
        }

        function openAddExperienceModal() {
            document.getElementById('addExperienceModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
            resetForm();
        }

        function closeAddExperienceModal() {
            document.getElementById('addExperienceModal').style.display = 'none';
            document.body.style.overflow = '';
            hideMessage();
        }

        function toggleNewCompanyInput() {
            const newCompanyInput = document.getElementById('newCompanyInput');
            const companySelect = document.getElementById('companyName');
            
            if (newCompanyInput.classList.contains('active')) {
                newCompanyInput.classList.remove('active');
                companySelect.disabled = false;
                document.getElementById('newCompanyName').value = '';
            } else {
                newCompanyInput.classList.add('active');
                companySelect.disabled = true;
                companySelect.value = '';
                document.getElementById('newCompanyName').focus();
            }
        }

        function handleFormSubmit(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitBtn');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
            
            const formData = new FormData();
            
            const registerNo = document.getElementById('registerNo').value.trim();
            const studentName = document.getElementById('studentName').value.trim().toUpperCase();
            const phoneNo = document.getElementById('phoneNo').value.trim();
            const email = document.getElementById('email').value.trim();
            const yearOfGraduation = document.getElementById('yearOfGraduation').value;
            const package = document.getElementById('package').value;
            const companyType = document.getElementById('companyType').value;
            
            let companyName = '';
            const newCompanyInput = document.getElementById('newCompanyInput');
            if (newCompanyInput.classList.contains('active')) {
                companyName = document.getElementById('newCompanyName').value.trim();
            } else {
                companyName = document.getElementById('companyName').value;
            }
            
            const rounds = {};
            for (let i = 1; i <= 6; i++) {
                const roundValue = document.getElementById(`round${i}`).value.trim();
                if (roundValue) {
                    rounds[`round${i}`] = roundValue;
                }
            }
            
            if (!registerNo || !studentName || !yearOfGraduation || !companyName || !package || !companyType) {
                showMessage('Please fill in all required fields.', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                return;
            }
            
            formData.append('registerNo', registerNo);
            formData.append('studentName', studentName);
            formData.append('phoneNo', phoneNo);
            formData.append('email', email);
            formData.append('yearOfGraduation', yearOfGraduation);
            formData.append('companyName', companyName);
            formData.append('companyType', companyType);
            formData.append('package', package);
            formData.append('editMode', document.getElementById('editMode').value);
            formData.append('originalRegisterNo', document.getElementById('originalRegisterNo').value);
            formData.append('originalCompanyName', document.getElementById('originalCompanyName').value);
            
            Object.keys(rounds).forEach(key => {
                formData.append(key, rounds[key]);
            });
            
            if (newCompanyInput.classList.contains('active')) {
                formData.append('isNewCompany', 'true');
            }
            
            fetch('submit_experience.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then(text => {
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        showMessage(data.message, 'success');
                        setTimeout(() => {
                            resetForm();
                            closeAddExperienceModal();
                            if (currentBatch) {
                                loadCompanies(currentBatch);
                            }
                            loadCompaniesForDropdown();
                        }, 2000);
                    } else {
                        showMessage(data.message || 'Unknown error occurred', 'error');
                    }
                } catch (parseError) {
                    showMessage('Server returned invalid response: ' + text.substring(0, 200), 'error');
                }
            })
            .catch(error => {
                showMessage('Network error: ' + error.message, 'error');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        }

        function showMessage(message, type) {
            const messageDiv = document.getElementById('formMessage');
            messageDiv.textContent = message;
            messageDiv.className = `message ${type} active`;
        }

        function hideMessage() {
            const messageDiv = document.getElementById('formMessage');
            messageDiv.className = 'message';
        }

        function resetForm() {
            const form = document.getElementById('addExperienceForm');
            form.reset();
            
            document.getElementById('editMode').value = 'false';
            document.getElementById('originalRegisterNo').value = '';
            document.getElementById('originalCompanyName').value = '';
            document.getElementById('editModeHeader').style.display = 'none';
            document.getElementById('formTitle').textContent = 'Add Your Placement Experience';
            
            const newCompanyInput = document.getElementById('newCompanyInput');
            newCompanyInput.classList.remove('active');
            document.getElementById('companyName').disabled = false;
            
            const textAreas = ['round1', 'round2', 'round3', 'round4', 'round5', 'round6'];
            textAreas.forEach(roundId => {
                updateCharacterCount(roundId);
            });
            
            hideMessage();
        }

        function editExperience(registerNo, companyName) {
            fetch(`get_experience.php?register_no=${registerNo}&company=${encodeURIComponent(companyName)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.experience) {
                        populateEditForm(data.experience, registerNo, companyName);
                        openAddExperienceModal();
                    } else {
                        alert('Error loading experience data');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading experience data');
                });
        }

        function populateEditForm(experience, registerNo, companyName) {
            fetch(`get_student_data.php?register_no=${registerNo}`)
                .then(response => response.json())
                .then(studentData => {
                    if (studentData.success) {
                        const student = studentData.student;
                        
                        document.getElementById('editMode').value = 'true';
                        document.getElementById('originalRegisterNo').value = registerNo;
                        document.getElementById('originalCompanyName').value = companyName;
                        document.getElementById('editModeHeader').style.display = 'block';
                        document.getElementById('formTitle').textContent = 'Edit Your Placement Experience';
                        
                        document.getElementById('registerNo').value = registerNo;
                        document.getElementById('studentName').value = student.name;
                        document.getElementById('phoneNo').value = student.phone_no || '';
                        document.getElementById('email').value = student.mail || '';
                        document.getElementById('yearOfGraduation').value = student.year_of_graduation;
                        document.getElementById('package').value = parseFloat(experience.package);
                        document.getElementById('companyType').value = experience.company_type || '';
                        
                        setTimeout(() => {
                            document.getElementById('companyName').value = companyName;
                        }, 100);
                        
                        const rounds = ['round1', 'round2', 'round3', 'round4', 'round5', 'round6'];
                        rounds.forEach(round => {
                            if (experience[round]) {
                                document.getElementById(round).value = experience[round];
                                updateCharacterCount(round);
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error('Error loading student data:', error);
                });
        }

        function loadBatches() {
            fetch('get_batches.php')
                .then(response => response.json())
                .then(data => {
                    const batchGrid = document.getElementById('batchGrid');
                    if (data.success && data.batches.length > 0) {
                        batchGrid.innerHTML = '';
                        data.batches.forEach(batch => {
                            const batchCard = document.createElement('button');
                            batchCard.className = 'batch-card';
                            batchCard.innerHTML = `
                                <i class="fas fa-calendar-alt"></i>
                                <div style="margin-top: 10px;">Batch ${batch.year_of_graduation}</div>
                                <div style="font-size: 14px; opacity: 0.8;">${batch.student_count} students placed</div>
                            `;
                            batchCard.onclick = () => loadCompanies(batch.year_of_graduation);
                            batchGrid.appendChild(batchCard);
                        });
                    } else {
                        batchGrid.innerHTML = '<div class="no-data">No placement data available</div>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('batchGrid').innerHTML = '<div class="no-data">Error loading batches</div>';
                });
        }

        function loadCompanies(batch) {
            currentBatch = batch;
            document.getElementById('companyTableTitle').textContent = `Companies - Batch ${batch}`;
            document.getElementById('companyTableContent').innerHTML = '<div class="loading">Loading companies...</div>';
            
            showCompanyTable();

            fetch(`get_companies.php?batch=${batch}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.companies.length > 0) {
                        let tableHTML = `
                            <table>
                                <thead>
                                    <tr>
                                        <th>S.No</th>
                                        <th>Company Name</th>
                                        <th>Students Placed</th>
                                        <th>Avg Package (LPA)</th>
                                        <th>Max Package (LPA)</th>
                                    </tr>
                                </thead>
                                <tbody>
                        `;
                        
                        data.companies.forEach((company, index) => {
                            tableHTML += `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td><span class="company-name" onclick="loadStudents('${company.company_name}')">${company.company_name}</span></td>
                                    <td>${company.student_count}</td>
                                    <td>${parseFloat(company.avg_package).toFixed(2)}</td>
                                    <td>${parseFloat(company.max_package).toFixed(2)}</td>
                                </tr>
                            `;
                        });
                        
                        tableHTML += '</tbody></table>';
                        document.getElementById('companyTableContent').innerHTML = tableHTML;
                    } else {
                        document.getElementById('companyTableContent').innerHTML = '<div class="no-data">No companies found for this batch</div>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('companyTableContent').innerHTML = '<div class="no-data">Error loading companies</div>';
                });
        }

        function loadStudents(companyName) {
            currentCompany = companyName;
            document.getElementById('studentTableTitle').textContent = `Students - ${companyName}`;
            document.getElementById('studentTableContent').innerHTML = '<div class="loading">Loading students...</div>';
            
            showStudentTable();

            fetch(`get_students.php?batch=${currentBatch}&company=${encodeURIComponent(companyName)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.students.length > 0) {
                        let tableHTML = `
                            <table>
                                <thead>
                                    <tr>
                                        <th>S.No</th>
                                        <th>Name</th>
                                        <th>Register No</th>
                                        <th>Phone</th>
                                        <th>Email</th>
                                        <th>Package (LPA)</th>
                                        <th>Experience</th>
                                        ${canEdit ? '<th>Actions</th>' : ''}
                                    </tr>
                                </thead>
                                <tbody>
                        `;
                        
                        data.students.forEach((student, index) => {
                            const actionButtons = canEdit ? 
                                `<td>
                                    <button class="experience-link" style="background: #f39c12;" onclick="editExperience('${student.register_no}', '${currentCompany}')">Edit</button>
                                    <button class="delete-btn" onclick="deleteExperience('${student.register_no}', '${currentCompany}', '${student.name}')"><i class="fas fa-trash"></i> Delete</button>
                                </td>` : 
                                '';
                            
                            tableHTML += `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${student.name}</td>
                                    <td>${student.register_no}</td>
                                    <td>${student.phone_no || 'N/A'}</td>
                                    <td>${student.mail || 'N/A'}</td>
                                    <td>${parseFloat(student.package).toFixed(2)}</td>
                                    <td><button class="experience-link" onclick="showExperience('${student.register_no}', '${student.name}', '${currentCompany}')">View Experience</button></td>
                                    ${actionButtons}
                                </tr>
                            `;
                        });
                        
                        tableHTML += '</tbody></table>';
                        document.getElementById('studentTableContent').innerHTML = tableHTML;
                    } else {
                        document.getElementById('studentTableContent').innerHTML = '<div class="no-data">No students found</div>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('studentTableContent').innerHTML = '<div class="no-data">Error loading students</div>';
                });
        }

        function showExperience(registerNo, studentName, companyName) {
            document.getElementById('modalStudentName').textContent = studentName;
            document.getElementById('modalCompanyName').textContent = companyName;
            document.getElementById('modalContent').innerHTML = '<div class="loading">Loading experience...</div>';
            
            document.getElementById('experienceModal').style.display = 'block';

            fetch(`get_experience.php?register_no=${registerNo}&company=${encodeURIComponent(companyName)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.experience) {
                        let contentHTML = '';
                        const rounds = ['round1', 'round2', 'round3', 'round4', 'round5', 'round6'];
                        
                        rounds.forEach((round, index) => {
                            if (data.experience[round] && data.experience[round].trim() !== '') {
                                contentHTML += `
                                    <div class="round-section">
                                        <div class="round-title">
                                            <i class="fas fa-circle"></i>
                                            Round ${index + 1}
                                        </div>
                                        <div class="round-content">${data.experience[round]}</div>
                                    </div>
                                `;
                            }
                        });
                        
                        if (contentHTML === '') {
                            contentHTML = '<div class="no-data">No experience details available</div>';
                        }
                        
                        document.getElementById('modalContent').innerHTML = contentHTML;
                    } else {
                        document.getElementById('modalContent').innerHTML = '<div class="no-data">No experience details found</div>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('modalContent').innerHTML = '<div class="no-data">Error loading experience</div>';
                });
        }

        function deleteExperience(registerNo, companyName, studentName) {
            deleteRegisterNo = registerNo;
            deleteCompanyName = companyName;
            
            document.getElementById('confirmMessage').innerHTML = 
                `Are you sure you want to delete the placement experience of<br><strong>${studentName}</strong> at <strong>${companyName}</strong>?<br><br>This action cannot be undone.`;
            document.getElementById('confirmModal').style.display = 'block';
        }

        function closeConfirmModal() {
            document.getElementById('confirmModal').style.display = 'none';
            deleteRegisterNo = null;
            deleteCompanyName = null;
        }

        function confirmDelete() {
            if (!deleteRegisterNo || !deleteCompanyName) {
                closeConfirmModal();
                return;
            }

            const formData = new FormData();
            formData.append('register_no', deleteRegisterNo);
            formData.append('company_name', deleteCompanyName);

            fetch('delete_experience.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Experience deleted successfully!');
                    closeConfirmModal();
                    loadStudents(currentCompany);
                    loadCompaniesForDropdown();
                } else {
                    alert('Error deleting experience: ' + (data.message || 'Unknown error'));
                    closeConfirmModal();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error deleting experience: ' + error.message);
                closeConfirmModal();
            });
        }

        function showBatchSelection() {
            document.getElementById('batchContainer').style.display = 'block';
            document.getElementById('companyTableContainer').classList.remove('active');
            document.getElementById('studentTableContainer').classList.remove('active');
        }

        function showCompanyTable() {
            document.getElementById('batchContainer').style.display = 'none';
            document.getElementById('companyTableContainer').classList.add('active');
            document.getElementById('studentTableContainer').classList.remove('active');
        }

        function showStudentTable() {
            document.getElementById('batchContainer').style.display = 'none';
            document.getElementById('companyTableContainer').classList.remove('active');
            document.getElementById('studentTableContainer').classList.add('active');
        }

        function closeModal() {
            document.getElementById('experienceModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('experienceModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
            
            const confirmModal = document.getElementById('confirmModal');
            if (event.target === confirmModal) {
                confirmModal.style.display = 'none';
            }
        }
    </script>
</body>
</html>