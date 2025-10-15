<?php
session_start();

// Check if user is logged in and is admin or faculty
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header('Location: index.html');
    exit();
}

$currentUserType = $_SESSION['db_user_type'] ?? $_SESSION['user_type'];

// Only admin and faculty can access this page
if (!in_array($currentUserType, ['admin', 'faculty'])) {
    header('Location: dashboard.php');
    exit();
}

$currentUserId = $_SESSION['user_id'];
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
        
        .nav-links {
            display: flex;
            gap: 20px;
        }
        
        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 8px 12px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .nav-links a:hover {
            background-color: #34495e;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
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
            
            .nav-links {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="logo-container">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/4/43/Itechlogo.png/738px-Itechlogo.png" alt="PSG iTech Logo" class="logo">
            <span class="nav-title">Admin Panel</span>
        </div>
        <div class="nav-links">
            <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            <a href="placement_experience.php"><i class="fas fa-book"></i> Placements</a>
            <a href="logout.php" style="background: #e74c3c;"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
        <div class="user-info">
            <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <small style="font-size: 11px; opacity: 0.8;">(<?php echo ucfirst($currentUserType); ?>)</small>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-user-shield"></i> Admin Control Panel</h1>
            <p>Manage users and placement details</p>
        </div>

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
                                    <label for="userId_0">User ID *</label>
                                    <input type="text" id="userId_0" name="userId[]" required placeholder="e.g., 22EE108">
                                </div>
                                <div class="form-group">
                                    <label for="userPassword_0">Password *</label>
                                    <input type="password" id="userPassword_0" name="userPassword[]" required placeholder="Enter password">
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
                            <th>User ID</th>
                            <th>User Type</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody">
                        <tr>
                            <td colspan="3" class="loading">Loading users...</td>
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
                user.user_id.toLowerCase().includes(searchValue) ||
                user.user_type.toLowerCase().includes(searchValue)
            );

            // Limit to 10 users
            filtered = filtered.slice(0, 10);

            if (filtered.length > 0) {
                tbody.innerHTML = '';
                filtered.forEach(user => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${user.user_id}</td>
                        <td><span style="padding: 4px 12px; background: ${getUserTypeColor(user.user_type)}; color: white; border-radius: 12px; font-size: 12px;">${user.user_type.toUpperCase()}</span></td>
                        <td class="action-buttons">
                            <button class="btn btn-warning btn-small" onclick="editUser('${user.user_id}', '${user.user_type}')">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-danger btn-small" onclick="deleteUser('${user.user_id}')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="3" style="text-align: center;">No users found</td></tr>';
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
        function editUser(userId, userType) {
            document.getElementById('editUserId').value = userId;
            document.getElementById('editUserType').value = userType;
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
        function deleteUser(userId) {
            if (!confirm(`Are you sure you want to delete user: ${userId}?`)) {
                return;
            }
            
            const formData = new FormData();
            formData.append('userId', userId);
            
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
                    <label for="userId_${userIndex}">User ID *</label>
                    <input type="text" id="userId_${userIndex}" name="userId[]" required placeholder="e.g., 22EE108">
                </div>
                <div class="form-group">
                    <label for="userPassword_${userIndex}">Password *</label>
                    <input type="password" id="userPassword_${userIndex}" name="userPassword[]" required placeholder="Enter password">
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