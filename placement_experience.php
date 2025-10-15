<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header('Location: index.html');
    exit();
}

// Get user information
$currentUserId = $_SESSION['user_id'];
$currentUserType = $_SESSION['db_user_type'] ?? $_SESSION['user_type'];

// Determine if user can edit (only admin and faculty)
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
        
        /* Navbar Styles */
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
        
        .user-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        /* Mobile Menu Button */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
            padding: 5px;
        }
        
        /* Container */
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
        
        .page-header p {
            color: #7f8c8d;
            font-size: 18px;
        }
        
        /* Batch Selection */
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
        
        .batch-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }
        
        /* Company Table */
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
        
        .back-btn:hover {
            background: #2980b9;
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
        
        .company-name:hover {
            color: #2980b9;
            text-decoration: underline;
        }
        
        /* Student Table */
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
            text-decoration: none;
            font-size: 12px;
            cursor: pointer;
            border: none;
        }
        
        .experience-link:hover {
            background: #c0392b;
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
        
        .modal-close:hover {
            color: #000;
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
        
        .modal-header p {
            color: #7f8c8d;
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
        
        /* Responsive */
        @media (max-width: 768px) {
            .navbar {
                padding: 15px 20px;
            }
            
            .nav-links {
                display: none;
            }
            
            .user-info {
                display: none;
            }
            
            .mobile-menu-btn {
                display: block;
            }
            
            .container {
                padding: 0 15px;
            }
            
            .modal-content {
                width: 95%;
                margin: 2% auto;
                padding: 20px;
            }
            
            .batch-grid {
                grid-template-columns: 1fr;
            }
            
            table {
                font-size: 14px;
            }
            
            th, td {
                padding: 8px 10px;
            }
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

        /* Add Experience Button */
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
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
        }

        .add-experience-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(231, 76, 60, 0.4);
        }

        /* Add Experience Modal */
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

        .add-experience-modal-close:hover {
            color: #000;
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

        .form-header p {
            color: #7f8c8d;
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

        .form-group.full-width {
            grid-column: 1 / -1;
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
            transition: border-color 0.3s ease;
            font-family: inherit;
        }

        .form-group input:focus, 
        .form-group select:focus, 
        .form-group textarea:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        .company-select-group {
            position: relative;
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

        .add-company-option:hover {
            background: #c0392b;
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
            padding: 12px 20px;      /* Add padding for a bigger box */
            background: #f4f6fa;     /* Optional: subtle background */
            border-radius: 8px;      /* Optional: rounded corners */
            box-sizing: border-box;  /* Ensure padding is included in size */
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
            transition: all 0.3s ease;
        }

        .btn-cancel {
            background: #95a5a6;
            color: white;
        }

        .btn-cancel:hover {
            background: #7f8c8d;
        }

        .btn-submit {
            background: #27ae60;
            color: white;
        }

        .btn-submit:hover {
            background: #229954;
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Success/Error Messages */
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

        /* Edit Mode Styles */
        .edit-mode-header {
            background: #f39c12;
            color: white;
            padding: 10px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .add-experience-modal-content {
                width: 95%;
                padding: 20px;
            }
            
            .form-actions {
                flex-direction: column;
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
        <div class="nav-links">

            <a href="dashboard.php"><i class="fas fa-home"></i> DASHBOARD</a>
            <a href="chatbot.html"><i class="fas fa-pencil"></i> PREP WITH AI</a>
        </div>
        <div class="user-info">
            <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <small style="font-size: 11px; opacity: 0.8;">(<?php echo ucfirst($currentUserType); ?>)</small>
        </div>
        <button class="mobile-menu-btn" id="mobileMenuBtn">
            <i class="fas fa-bars"></i>
        </button>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-trophy"></i> Placement Experience</h1>
            <p>Explore placement experiences of our students across different batches</p>
        </div>

        <!-- Add Experience Button -->
        <?php if ($canEdit): ?>
        <div class="add-experience-container">
            <button class="add-experience-btn" onclick="openAddExperienceModal()">
                <i class="fas fa-plus"></i> Add Your Experience
            </button>
        </div>
        <?php endif; ?>

        <!-- Batch Selection -->
        <div class="batch-container" id="batchContainer">
            <h2><i class="fas fa-graduation-cap"></i> Select Graduation Year</h2>
            <div class="batch-grid" id="batchGrid">
                <!-- Batches will be loaded here -->
            </div>
        </div>

        <!-- Company Table -->
        <div class="company-table-container" id="companyTableContainer">
            <div class="table-header">
                <h2 id="companyTableTitle">Companies - Batch 2024</h2>
                <button class="back-btn" onclick="showBatchSelection()">
                    <i class="fas fa-arrow-left"></i> Back to Batches
                </button>
            </div>
            <div id="companyTableContent">
                <!-- Company table will be loaded here -->
            </div>
        </div>

        <!-- Student Table -->
        <div class="student-table-container" id="studentTableContainer">
            <div class="table-header">
                <h2 id="studentTableTitle">Students - Company Name</h2>
                <button class="back-btn" onclick="showCompanyTable()">
                    <i class="fas fa-arrow-left"></i> Back to Companies
                </button>
            </div>
            <div id="studentTableContent">
                <!-- Student table will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Experience Modal -->
    <div id="experienceModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal()">&times;</span>
            <div class="modal-header">
                <h2 id="modalStudentName">Student Name</h2>
                <p id="modalCompanyName">Company Name</p>
            </div>
            <div id="modalContent">
                <!-- Experience rounds will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Add Experience Modal -->
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
                        <input type="text" id="registerNo" name="registerNo" required placeholder="e.g., 715522105008, 22ee108">
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
                    <div class="form-group company-select-group">
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
                    <h3><i class="fas fa-clipboard-list"></i> Interview Rounds Experience</h3>
                    
                    <div class="round-group">
                        <div class="round-label">
                            <i class="fas fa-circle"></i>
                            <span>Round 1</span>
                        </div>
                        <textarea id="round1" name="round1" placeholder="Describe your experience in Round 1 (e.g., Online test, aptitude, etc.)"></textarea>
                        <div class="char-count" id="round1Count">0 / 65535 characters</div>
                    </div>

                    <div class="round-group">
                        <div class="round-label">
                            <i class="fas fa-circle"></i>
                            <span>Round 2</span>
                        </div>
                        <textarea id="round2" name="round2" placeholder="Describe your experience in Round 2 (e.g., Technical interview, coding, etc.)"></textarea>
                        <div class="char-count" id="round2Count">0 / 65535 characters</div>
                    </div>

                    <div class="round-group">
                        <div class="round-label">
                            <i class="fas fa-circle"></i>
                            <span>Round 3</span>
                        </div>
                        <textarea id="round3" name="round3" placeholder="Describe your experience in Round 3 (e.g., HR round, managerial interview, etc.)"></textarea>
                        <div class="char-count" id="round3Count">0 / 65535 characters</div>
                    </div>

                    <div class="round-group">
                        <div class="round-label">
                            <i class="fas fa-circle"></i>
                            <span>Round 4</span>
                        </div>
                        <textarea id="round4" name="round4" placeholder="Describe your experience in Round 4 (if applicable)"></textarea>
                        <div class="char-count" id="round4Count">0 / 65535 characters</div>
                    </div>

                    <div class="round-group">
                        <div class="round-label">
                            <i class="fas fa-circle"></i>
                            <span>Round 5</span>
                        </div>
                        <textarea id="round5" name="round5" placeholder="Describe your experience in Round 5 (if applicable)"></textarea>
                        <div class="char-count" id="round5Count">0 / 65535 characters</div>
                    </div>

                    <div class="round-group">
                        <div class="round-label">
                            <i class="fas fa-circle"></i>
                            <span>Round 6</span>
                        </div>
                        <textarea id="round6" name="round6" placeholder="Describe your experience in Round 6 (if applicable)"></textarea>
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
        let currentBatch = null;
        let currentCompany = null;
        let companies = [];
        let canEdit = <?php echo json_encode($canEdit); ?>; // Pass PHP variable to JavaScript

        // Load batches on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadBatches();
            loadCompaniesForDropdown();
            initializeCharacterCounters();
            initializeFormValidation();
        });

        // Load available companies for dropdown
        function loadCompaniesForDropdown() {
            const companySelect = document.getElementById('companyName');
            companySelect.innerHTML = '<option value="">Loading companies...</option>';
            
            // Set a timeout for the request
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 5000); // 5 second timeout
            
            fetch('get_all_companies.php', {
                signal: controller.signal
            })
            .then(response => {
                clearTimeout(timeoutId);
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                companies = data.companies || [];
                
                companySelect.innerHTML = '<option value="">Select Company</option>';
                
                if (companies.length === 0) {
                    // Add default companies if none found
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
                
                // Load default companies on error
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

        // Initialize character counters for text areas
        function initializeCharacterCounters() {
            const textAreas = ['round1', 'round2', 'round3', 'round4', 'round5', 'round6'];
            textAreas.forEach(roundId => {
                const textArea = document.getElementById(roundId);
                const counter = document.getElementById(roundId + 'Count');
                
                textArea.addEventListener('input', function() {
                    updateCharacterCount(roundId);
                });
            });
        }

        // Update character count and show warnings
        function updateCharacterCount(roundId) {
            const textArea = document.getElementById(roundId);
            const counter = document.getElementById(roundId + 'Count');
            const maxLength = 65535; // MySQL TEXT field limit
            const currentLength = textArea.value.length;
            
            counter.textContent = `${currentLength} / ${maxLength} characters`;
            
            // Remove existing classes
            counter.classList.remove('warning', 'danger');
            
            if (currentLength > maxLength * 0.9) {
                counter.classList.add('danger');
            } else if (currentLength > maxLength * 0.8) {
                counter.classList.add('warning');
            }
            
            // Prevent typing beyond limit
            if (currentLength >= maxLength) {
                textArea.value = textArea.value.substring(0, maxLength);
                counter.textContent = `${maxLength} / ${maxLength} characters (Limit reached!)`;
                counter.classList.add('danger');
            }
        }

        // Initialize form validation
        function initializeFormValidation() {
            const studentNameInput = document.getElementById('studentName');
            studentNameInput.addEventListener('input', function() {
                this.value = this.value.toUpperCase();
            });

            const form = document.getElementById('addExperienceForm');
            form.addEventListener('submit', handleFormSubmit);
        }

        // Open add experience modal
        function openAddExperienceModal() {
            document.getElementById('addExperienceModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
            resetForm();
        }

        // Close add experience modal
        function closeAddExperienceModal() {
            document.getElementById('addExperienceModal').style.display = 'none';
            document.body.style.overflow = '';
            hideMessage();
        }

        // Toggle new company input
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

        // Handle form submission
        function handleFormSubmit(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitBtn');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
            
            const formData = new FormData();
            
            // Get form values
            const registerNo = document.getElementById('registerNo').value.trim();
            const studentName = document.getElementById('studentName').value.trim().toUpperCase();
            const phoneNo = document.getElementById('phoneNo').value.trim();
            const email = document.getElementById('email').value.trim();
            const yearOfGraduation = document.getElementById('yearOfGraduation').value;
            const package = document.getElementById('package').value;
            const companyType = document.getElementById('companyType').value;
            
            // Get company name (either from dropdown or new input)
            let companyName = '';
            const newCompanyInput = document.getElementById('newCompanyInput');
            if (newCompanyInput.classList.contains('active')) {
                companyName = document.getElementById('newCompanyName').value.trim();
            } else {
                companyName = document.getElementById('companyName').value;
            }
            
            // Get round data
            const rounds = {};
            for (let i = 1; i <= 6; i++) {
                const roundValue = document.getElementById(`round${i}`).value.trim();
                if (roundValue) {
                    rounds[`round${i}`] = roundValue;
                }
            }
            
            // Validation
            if (!registerNo || !studentName || !yearOfGraduation || !companyName || !package || !companyType) {
                showMessage('Please fill in all required fields.', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                return;
            }
            
            // Prepare form data
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
            
            // Add round data
            Object.keys(rounds).forEach(key => {
                formData.append(key, rounds[key]);
            });
            
            // Add new company flag
            if (newCompanyInput.classList.contains('active')) {
                formData.append('isNewCompany', 'true');
            }
            
            // Submit form
            fetch('submit_experience.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                return response.text(); // Get as text first
            })
            .then(text => {
                console.log('Raw response:', text);
                
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        showMessage(data.message, 'success');
                        
                        // Reset form after success
                        setTimeout(() => {
                            resetForm();
                            closeAddExperienceModal();
                            
                            // Refresh data if we're viewing a batch
                            if (currentBatch) {
                                loadCompanies(currentBatch);
                            }
                            
                            // Refresh company dropdown
                            loadCompaniesForDropdown();
                        }, 2000);
                    } else {
                        showMessage(data.message || 'Unknown error occurred', 'error');
                    }
                } catch (parseError) {
                    console.error('JSON parse error:', parseError);
                    showMessage('Server returned invalid response: ' + text.substring(0, 200), 'error');
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                showMessage('Network error: ' + error.message + '. Please check if submit_experience.php exists and your server is running.', 'error');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        }

        // Show message
        function showMessage(message, type) {
            const messageDiv = document.getElementById('formMessage');
            messageDiv.textContent = message;
            messageDiv.className = `message ${type} active`;
        }

        // Hide message
        function hideMessage() {
            const messageDiv = document.getElementById('formMessage');
            messageDiv.className = 'message';
        }

        // Reset form
        function resetForm() {
            const form = document.getElementById('addExperienceForm');
            form.reset();
            
            // Reset edit mode
            document.getElementById('editMode').value = 'false';
            document.getElementById('originalRegisterNo').value = '';
            document.getElementById('originalCompanyName').value = '';
            document.getElementById('editModeHeader').style.display = 'none';
            document.getElementById('formTitle').textContent = 'Add Your Placement Experience';
            
            // Reset new company input
            const newCompanyInput = document.getElementById('newCompanyInput');
            newCompanyInput.classList.remove('active');
            document.getElementById('companyName').disabled = false;
            
            // Reset character counters
            const textAreas = ['round1', 'round2', 'round3', 'round4', 'round5', 'round6'];
            textAreas.forEach(roundId => {
                updateCharacterCount(roundId);
            });
            
            hideMessage();
        }

        // Edit experience function (to be called from student table)
        function editExperience(registerNo, companyName) {
            // Load experience data
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

        // Populate form with existing data for editing
        function populateEditForm(experience, registerNo, companyName) {
            // Load student data first
            fetch(`get_student_data.php?register_no=${registerNo}`)
                .then(response => response.json())
                .then(studentData => {
                    if (studentData.success) {
                        const student = studentData.student;
                        
                        // Set form to edit mode
                        document.getElementById('editMode').value = 'true';
                        document.getElementById('originalRegisterNo').value = registerNo;
                        document.getElementById('originalCompanyName').value = companyName;
                        document.getElementById('editModeHeader').style.display = 'block';
                        document.getElementById('formTitle').textContent = 'Edit Your Placement Experience';
                        
                        // Populate form fields
                        document.getElementById('registerNo').value = registerNo;
                        document.getElementById('studentName').value = student.name;
                        document.getElementById('phoneNo').value = student.phone_no || '';
                        document.getElementById('email').value = student.mail || '';
                        document.getElementById('yearOfGraduation').value = student.year_of_graduation;
                        document.getElementById('package').value = parseFloat(experience.package);
                        document.getElementById('companyType').value = experience.company_type || '';
                        
                        // Set company name
                        setTimeout(() => {
                            document.getElementById('companyName').value = companyName;
                        }, 100);
                        
                        // Populate rounds
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

        // Load available batches
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

        // Load companies for selected batch
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

        // Load students for selected company
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
                                        ${canEdit ? '<th>Edit</th>' : ''}
                                    </tr>
                                </thead>
                                <tbody>
                        `;
                        
                        data.students.forEach((student, index) => {
                            const editButton = canEdit ? 
                                `<td><button class="experience-link" style="background: #f39c12;" onclick="editExperience('${student.register_no}', '${currentCompany}')">Edit</button></td>` : 
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
                                    ${editButton}
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

        // Show experience modal
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

        // Navigation functions
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

        // Close modal
        function closeModal() {
            document.getElementById('experienceModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('experienceModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>