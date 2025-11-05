<?php
session_start();

// Check if user is logged in and is admin or faculty
if (!isset($_SESSION['mail_id']) || !isset($_SESSION['user_type'])) {
    header('Location: index.html');
    exit();
}

$currentUserType = $_SESSION['user_type'];

// Only admin and faculty can access this page
if (!in_array($currentUserType, ['admin', 'faculty'])) {
    header('Location: dashboard.php');
    exit();
}

$currentUserId = $_SESSION['mail_id'];
$username = $_SESSION['user_name'] ?? 'User';
$firstLetter = strtoupper(substr($username, 0, 1));
$currentYear = date('Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - EEE Department</title>
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
        
        /* Navbar */
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
        
        .nav-title {
            font-size: 22px;
            font-weight: bold;
        }
        
        .nav-content {
            display: flex;
            align-items: center;
            gap: 30px;
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
        
        /* Container */
        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .page-header h1 {
            font-size: 32px;
            margin-bottom: 5px;
        }
        
        .page-header p {
            opacity: 0.9;
        }
        
        /* Section Cards */
        .section-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #ecf0f1;
        }
        
        .section-header h2 {
            color: #2c3e50;
            font-size: 24px;
        }
        
        /* Buttons */
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: #3498db;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }
        
        .btn-success {
            background: #27ae60;
            color: white;
        }
        
        .btn-success:hover {
            background: #229954;
        }
        
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c0392b;
        }
        
        .btn-warning {
            background: #f39c12;
            color: white;
        }
        
        .btn-warning:hover {
            background: #e67e22;
        }
        
        /* Form Styles */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group label {
            font-weight: 600;
            margin-bottom: 8px;
            color: #2c3e50;
        }
        
        .form-group input,
        .form-group select {
            padding: 10px 12px;
            border: 2px solid #ecf0f1;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }
        
        /* Table Styles */
        .table-container {
            max-height: 420px; /* About 10 rows */
            overflow-y: auto;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
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
            position: sticky;
            top: 0;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .btn-small {
            padding: 6px 12px;
            font-size: 12px;
        }
        
        /* Batch Buttons */
        .batch-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .batch-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        
        .batch-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }
        
        .batch-card h3 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .batch-card p {
            font-size: 14px;
            opacity: 0.9;
        }
        
        /* Message Box */
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
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            overflow-y: auto;
        }
        
        .modal-content {
            background-color: white;
            margin: 3% auto;
            padding: 0;
            border-radius: 10px;
            width: 95%;
            max-width: 1200px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 30px;
            border-radius: 10px 10px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-close {
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            background: rgba(255, 255, 255, 0.2);
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .modal-close:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(90deg);
        }
        
        .modal-body {
            padding: 30px;
        }
        
        /* Spreadsheet iframe */
        .spreadsheet-container {
            margin-top: 20px;
        }
        
        .spreadsheet-info {
            background: #e8f5e9;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 15px;
            border-left: 4px solid #27ae60;
        }
        
        .spreadsheet-info h4 {
            color: #27ae60;
            margin-bottom: 8px;
        }
        
        .spreadsheet-info p {
            color: #2c3e50;
            font-size: 14px;
        }
        
        iframe {
            width: 100%;
            height: 600px;
            border: 2px solid #ecf0f1;
            border-radius: 8px;
        }
        
        /* Loading */
        .loading {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
        }
        
        @media (max-width: 1200px) {
            .nav-links, .user-info {
                display: none;
            }
            .mobile-menu-btn {
                display: block;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 15px;
            }
            .section-card {
                padding: 20px 15px;
            }
            .form-grid {
                grid-template-columns: 1fr;
            }
            .table-container {
                overflow-x: scroll;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="logo-container">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/4/43/Itechlogo.png/738px-Itechlogo.png" alt="PSG iTech Logo" class="logo">
            <span class="nav-title">EEE Department</span>
        </div>
        
        <div class="nav-content">
            <div class="nav-links">
                <a href="dashboard.php"><i class="fas fa-home"></i> HOME</a>
                <a href="placement_experience.php"><i class="fas fa-book"></i> PLACED EXPERIENCE</a>
                <a href="chatbot.php"><i class="fas fa-pencil-alt"></i> PREP WITH AI</a>
                <?php if (in_array($currentUserType, ['admin', 'faculty'])): ?>
                    <a href="admin_panel.php" style="background: #34495e;"><i class="fas fa-user-shield"></i> Admin Panel</a>
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

    <!-- Mobile Menu -->
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

    <!-- Main Content -->
    <div class="container">
        <!-- Page Header -->

        <!-- Placement Details Section -->
        <div class="section-card">
            <div class="section-header">
                <h2><i class="fas fa-file-excel"></i> Placement Details Management</h2>
                <button class="btn btn-success" onclick="openCreateBatchModal()">
                    <i class="fas fa-plus"></i> Create New Batch
                </button>
            </div>

            <p style="color: #7f8c8d; margin-bottom: 20px;">
                Select a batch to view and edit placement details in Google Sheets
            </p>

            <div class="batch-grid" id="batchGrid">
                <!-- Batch buttons will be loaded here -->
            </div>
        </div>

        <!-- User Management Section (moved below Placement Details) -->
        <div class="section-card">
            <div class="section-header">
                <h2><i class="fas fa-users"></i> User Management</h2>
                <button class="btn btn-primary" onclick="toggleAddUserForm()">
                    <i class="fas fa-user-plus"></i> Add New User
                </button>
            </div>

            <div style="display: flex; gap: 10px; margin-bottom: 15px;">
                <input type="text" id="userSearch" placeholder="Search by User ID or Type..." style="padding: 10px; width: 100%; border-radius: 6px; border: 2px solid #ecf0f1; font-size: 15px;">
                <button type="button" class="btn btn-primary" onclick="renderUserTable(window.allUsers || [])">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>

            <div id="userMessage" class="message"></div>

            <!-- Add User Form -->
            <div id="addUserForm" style="display: none; margin-bottom: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                <h3 style="margin-bottom: 20px; color: #2c3e50;">Add New Users</h3>
                <form id="userForm" onsubmit="handleAddUser(event)">
                    <div id="userInputsContainer">
                        <!-- First user input -->
                        <div class="user-input-row" data-user-index="0">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="mailId_0">Email ID (Login) *</label>
                                    <input type="email" id="mailId_0" name="mailId[]" required placeholder="e.g., student@psgitech.ac.in">
                                </div>
                                <div class="form-group">
                                    <label for="userName_0">User Name *</label>
                                    <input type="text" id="userName_0" name="userName[]" required placeholder="e.g., John Doe">
                                </div>
                                <div class="form-group">
                                    <label for="userPassword_0">Password *</label>
                                    <input type="text" id="userPassword_0" name="userPassword[]" required placeholder="Enter password">
                                </div>
                                <div class="form-group">
                                    <label for="userType_0">User Type *</label>
                                    <select id="userType_0" name="userType[]" required>
                                        <option value="">Select Type</option>
                                        <option value="student">Student</option>
                                        <option value="faculty">Faculty</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                </div>
                            </div>
                            <hr style="margin: 15px 0; border: none; border-top: 1px dashed #ddd;">
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary" onclick="addAnotherUser()" style="margin-bottom: 15px;">
                        <i class="fas fa-plus"></i> Add Another User
                    </button>
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Add All Users
                        </button>
                        <button type="button" class="btn btn-danger" onclick="toggleAddUserForm()">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                    </div>
                </form>
            </div>

            <!-- Users Table -->
            <div class="table-container">
                <table id="usersTable">
                    <thead>
                        <tr>
                            <th>Email ID</th>
                            <th>User Name</th>
                            <th>User Type</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody">
                        <tr>
                            <td colspan="4" class="loading">Loading users...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editUserModal" class="modal">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h2>Edit User</h2>
                <span class="modal-close" onclick="closeEditUserModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="editUserForm" onsubmit="handleEditUser(event)">
                    <input type="hidden" id="editUserId" name="editUserId">
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="editUserName">User Name *</label>
                        <input type="text" id="editUserName" name="editUserName" required placeholder="Enter user name">
                    </div>
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="editUserType">User Type *</label>
                        <select id="editUserType" name="editUserType" required>
                            <option value="student">Student</option>
                            <option value="faculty">Faculty</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="editUserPassword">New Password (leave blank to keep current)</label>
                        <input type="password" id="editUserPassword" name="editUserPassword" placeholder="Enter new password">
                    </div>
                    <div style="display: flex; gap: 10px; justify-content: flex-end;">
                        <button type="button" class="btn btn-danger" onclick="closeEditUserModal()">Cancel</button>
                        <button type="submit" class="btn btn-success">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Create Batch Modal -->
    <div id="createBatchModal" class="modal">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h2>Add New Batch Sheet</h2>
                <span class="modal-close" onclick="closeCreateBatchModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div id="createBatchMessage" class="message"></div>
                
                <p style="margin-bottom: 20px; color: #7f8c8d;">
                    Add a new Google Sheet for the specified batch year. You can use any Google Sheets link.
                </p>
                
                <form id="createBatchForm" onsubmit="handleCreateBatch(event)">
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="batchYear">Graduation Year *</label>
                        <input type="number" id="batchYear" name="batchYear" required placeholder="e.g., 2010" min="2100" max="2035">
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="sheetUrl">Google Sheet URL *</label>
                        <input type="url" id="sheetUrl" name="sheetUrl" required placeholder="https://docs.google.com/spreadsheets/d/..." style="width: 100%;">
                        <small style="color: #7f8c8d; margin-top: 5px; display: block;">
                            Paste the full Google Sheets link from any account
                        </small>
                    </div>
                    
                    <div class="spreadsheet-info">
                        <h4><i class="fas fa-info-circle"></i> Instructions</h4>
                        <p><strong>1.</strong> Create a Google Sheet in your Google Drive</p>
                        <p><strong>2.</strong> Name it: EEE_placement_details_sheet_[YEAR]</p>
                        <p><strong>3.</strong> Share it with appropriate access (Editor/Viewer)</p>
                        <p><strong>4.</strong> Copy the sheet URL and paste above</p>
                    </div>
                    
                    <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                        <button type="button" class="btn btn-danger" onclick="closeCreateBatchModal()">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-plus"></i> Add Sheet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Spreadsheet Modal -->
    <div id="spreadsheetModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="spreadsheetTitle">Placement Details - Batch 2025</h2>
                <span class="modal-close" onclick="closeSpreadsheetModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="spreadsheet-info">
                    <h4><i class="fas fa-info-circle"></i> Instructions</h4>
                    <p>You can view and edit the placement details directly in the spreadsheet below. All changes are saved automatically to Google Sheets.</p>
                    <p style="margin-top: 8px;"><strong>Note:</strong> Make sure you're logged into the correct Google account (22ee108@psgitech.ac.in).</p>
                </div>
                
                <div class="spreadsheet-container">
                    <iframe id="spreadsheetFrame" src="" frameborder="0"></iframe>
                </div>
                
                <div style="margin-top: 15px; display: flex; gap: 10px;">
                    <button class="btn btn-primary" onclick="openInNewTab()">
                        <i class="fas fa-external-link-alt"></i> Open in New Tab
                    </button>
                    <button class="btn btn-success" onclick="refreshSpreadsheet()">
                        <i class="fas fa-sync"></i> Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentSpreadsheetUrl = '';
        
        // Load users on page load
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
        });

        document.addEventListener('DOMContentLoaded', function() {
            loadUsers();
            loadBatches();
        });
        
        // Toggle Add User Form
        function toggleAddUserForm() {
            const form = document.getElementById('addUserForm');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
            if (form.style.display === 'block') {
                document.getElementById('userForm').reset();
            }
        }
        
        // Load Users
        function loadUsers() {
            fetch('admin_get_users.php')
                .then(response => response.json())
                .then(data => {
                    window.allUsers = data.success ? data.users : [];
                    renderUserTable(window.allUsers);
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('usersTableBody').innerHTML = '<tr><td colspan="3" style="text-align: center; color: #e74c3c;">Error loading users</td></tr>';
                });
        }
        
        // Render table with filter and limit
        function renderUserTable(users) {
            const tbody = document.getElementById('usersTableBody');
            const searchValue = document.getElementById('userSearch').value.trim().toLowerCase();

            let filtered = users.filter(user =>
                user.mail_id.toLowerCase().includes(searchValue) ||
                (user.user_name && user.user_name.toLowerCase().includes(searchValue)) ||
                user.user_type.toLowerCase().includes(searchValue) 
            );

            // Limit to 10 users
            filtered = filtered.slice(0, 10);

            if (filtered.length > 0) {
                tbody.innerHTML = '';
                filtered.forEach(user => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${user.mail_id}</td>
                        <td>${user.user_name}</td>
                        <td><span style="padding: 4px 12px; background: ${getUserTypeColor(user.user_type)}; color: white; border-radius: 12px; font-size: 12px;">${user.user_type.toUpperCase()}</span></td>
                        <td class="action-buttons">
                            <button class="btn btn-warning btn-small" onclick="editUser('${user.mail_id}', '${user.user_name}', '${user.user_type}')">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-danger btn-small" onclick="deleteUser('${user.mail_id}')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="4" style="text-align: center;">No users found</td></tr>';
            }
        }
        
        function getUserTypeColor(type) {
            switch(type) {
                case 'admin': return '#e74c3c';
                case 'faculty': return '#f39c12';
                case 'student': return '#3498db';
                default: return '#95a5a6';
            }
        }
        
        // Handle Add User
        function handleAddUser(e) {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            
            fetch('admin_add_user.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                showMessage('userMessage', data.message, data.success ? 'success' : 'error');
                if (data.success) {
                    toggleAddUserForm();
                    loadUsers();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('userMessage', 'An error occurred', 'error');
            });
        }
        
        // Edit User
        function editUser(userId, userName, userType) {
            document.getElementById('editUserId').value = userId;
            document.getElementById('editUserType').value = userType;
            document.getElementById('editUserName').value = userName;
            document.getElementById('editUserPassword').value = '';
            document.getElementById('editUserModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
        
        function closeEditUserModal() {
            document.getElementById('editUserModal').style.display = 'none';
            document.body.style.overflow = '';
        }
        
        function handleEditUser(e) {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            
            fetch('admin_edit_user.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                showMessage('userMessage', data.message, data.success ? 'success' : 'error');
                if (data.success) {
                    closeEditUserModal();
                    loadUsers();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('userMessage', 'An error occurred', 'error');
            });
        }
        
        // Delete User
        function deleteUser(mailId) {
            if (!confirm(`Are you sure you want to delete user: ${mailId}?`)) {
                return;
            }
            
            const formData = new FormData();
            formData.append('mailId', mailId);
            
            fetch('admin_delete_user.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                showMessage('userMessage', data.message, data.success ? 'success' : 'error');
                if (data.success) {
                    loadUsers();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('userMessage', 'An error occurred', 'error');
            });
        }
        
        // Load Batches
        function loadBatches() {
            fetch('admin_get_batches.php')
                .then(response => response.json())
                .then(data => {
                    const grid = document.getElementById('batchGrid');
                    if (data.success && data.batches.length > 0) {
                        grid.innerHTML = '';
                        data.batches.forEach(batch => {
                            const card = document.createElement('button');
                            card.className = 'batch-card';
                            card.innerHTML = `
                                <h3><i class="fas fa-calendar-alt"></i> ${batch.year}</h3>
                                <p>View & Edit Details</p>
                            `;
                            card.onclick = () => openSpreadsheet(batch.year, batch.sheet_url);
                            grid.appendChild(card);
                        });
                    } else {
                        grid.innerHTML = '<p style="color: #7f8c8d; text-align: center; grid-column: 1 / -1;">No batches found. Create a new one to get started.</p>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('batchGrid').innerHTML = '<p style="color: #e74c3c;">Error loading batches</p>';
                });
        }
        
        // Open Spreadsheet
        function openSpreadsheet(year, url) {
            if (url) {
                window.open(url, '_blank');
            }
        }
        
        function closeSpreadsheetModal() {
            document.getElementById('spreadsheetModal').style.display = 'none';
            document.getElementById('spreadsheetFrame').src = '';
            document.body.style.overflow = '';
        }
        
        function openInNewTab() {
            if (currentSpreadsheetUrl) {
                window.open(currentSpreadsheetUrl, '_blank');
            }
        }
        
        function refreshSpreadsheet() {
            const frame = document.getElementById('spreadsheetFrame');
            frame.src = frame.src;
        }
        
        // Create Batch Modal
        function openCreateBatchModal() {
            document.getElementById('createBatchModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
        
        function closeCreateBatchModal() {
            document.getElementById('createBatchModal').style.display = 'none';
            document.getElementById('createBatchForm').reset();
            document.body.style.overflow = '';
            hideMessage('createBatchMessage');
        }
        
        function handleCreateBatch(e) {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const submitBtn = e.target.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
            
            fetch('admin_create_batch.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                showMessage('createBatchMessage', data.message, data.success ? 'success' : 'error');
                if (data.success) {
                    setTimeout(() => {
                        closeCreateBatchModal();
                        loadBatches();
                    }, 2000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('createBatchMessage', 'An error occurred', 'error');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-plus"></i> Create Sheet';
            });
        }
        
        // Helper Functions
        function showMessage(elementId, message, type) {
            const msgElement = document.getElementById(elementId);
            msgElement.textContent = message;
            msgElement.className = `message ${type} active`;
            
            setTimeout(() => {
                hideMessage(elementId);
            }, 5000);
        }
        
        function hideMessage(elementId) {
            const msgElement = document.getElementById(elementId);
            msgElement.className = 'message';
        }
        
        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
                document.body.style.overflow = '';
            }
        }
    </script>

    <!-- Add this script before </body> -->
    <script>
    let userIndex = 1; // Start from 1 since 0 is already present

    function addAnotherUser() {
        const container = document.getElementById('userInputsContainer');
        const row = document.createElement('div');
        row.className = 'user-input-row';
        row.setAttribute('data-user-index', userIndex);

        row.innerHTML = `
            <div class="form-grid">
                <div class="form-group">
                    <label for="mailId_${userIndex}">Email ID (Login) *</label>
                    <input type="email" id="mailId_${userIndex}" name="mailId[]" required placeholder="e.g., student@psgitech.ac.in">
                </div>
                <div class="form-group">
                    <label for="userName_${userIndex}">User Name *</label>
                    <input type="text" id="userName_${userIndex}" name="userName[]" required placeholder="e.g., Jane Doe">
                </div>
                <div class="form-group">
                    <label for="userPassword_${userIndex}">Password *</label>
                    <input type="text" id="userPassword_${userIndex}" name="userPassword[]" required placeholder="Enter password">
                </div>
                <div class="form-group">
                    <label for="userType_${userIndex}">User Type *</label>
                    <select id="userType_${userIndex}" name="userType[]" required>
                        <option value="">Select Type</option>
                        <option value="student">Student</option>
                        <option value="faculty">Faculty</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>
            <hr style="margin: 15px 0; border: none; border-top: 1px dashed #ddd;">
        `;
        container.appendChild(row);
        userIndex++;
    }
    </script>
</body>
</html>