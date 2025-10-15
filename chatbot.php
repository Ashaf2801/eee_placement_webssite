<?php
// chatbot.php - AI-POWERED CHAT INTERFACE
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header('Location: index.html');
    exit();
}

// Get user information
$currentUserId = $_SESSION['user_id'];
$currentUserType = $_SESSION['db_user_type'] ?? $_SESSION['user_type'];

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CRITICAL FIX STEP 1: Define the current directory
$script_dir = __DIR__; 

class AIChatSystem {
    private $db_config;
    private $gemini_api_key; 
    
    public function __construct() {
        // --- 1. Database Configuration (XAMPP Default) ---
        $this->db_config = [
            'host' => 'localhost',
            'user' => 'root',
            'password' => '', 
            'database' => 'eee_placement',
            'port' => 3306
        ];

        // --- 2. Gemini API Key Configuration ---
        $this->gemini_api_key = 'AIzaSyDqjHK3PUeV8hlYkCr-N4xAQwYw7gmP20Q';
    }
    
    public function getDBConnection() {
        try {
            $conn = new mysqli(
                $this->db_config['host'],
                $this->db_config['user'], 
                $this->db_config['password'],
                $this->db_config['database'],
                $this->db_config['port']
            );
            
            if ($conn->connect_error) {
                return ['success' => false, 'message' => "DB Connection failed: " . $conn->connect_error];
            }
            return $conn;
        } catch (Exception $e) {
            error_log("Database error: " . $e->getMessage());
            return ['success' => false, 'message' => "Database error: " . $e->getMessage()];
        }
    }
    
    public function runPythonAI($script_path, $args = []) {
        $env_prefix = "set GEMINI_API_KEY=" . escapeshellarg($this->gemini_api_key) . " && ";
        $env_prefix .= "set MYSQL_HOST=" . escapeshellarg($this->db_config['host']) . " && ";
        $env_prefix .= "set MYSQL_USER=" . escapeshellarg($this->db_config['user']) . " && ";
        $env_prefix .= "set MYSQL_PASSWORD=" . escapeshellarg($this->db_config['password']) . " && ";
        $env_prefix .= "set MYSQL_DATABASE=" . escapeshellarg($this->db_config['database']) . " && ";

        $python_executable = 'C:\\xampp\\htdocs\\login-system\\venv\\Scripts\\python.exe'; 

        $full_command = $env_prefix . escapeshellarg($python_executable) . " " . escapeshellarg($script_path); 

        foreach ($args as $arg) {
            $full_command .= " " . escapeshellarg($arg);
        }

        $full_command .= " 2>&1"; 

        $output_lines = [];
        $exit_code = 0;
        
        exec($full_command, $output_lines, $exit_code);
        
        $raw_output = implode("\n", $output_lines);

        $cleaned_lines = [];
        $start_capturing = false;
        
        foreach($output_lines as $line) {
            if (strpos($line, '🤖 Analyzing results with AI...') !== false) {
                $start_capturing = true; 
                continue;
            }
            
            if ($start_capturing) {
                 if (preg_match('/^(WARNING|E\d{4})/', trim($line))) {
                     continue; 
                 }
                $cleaned_lines[] = $line;
            }
        }
        
        $final_response = trim(implode("\n", $cleaned_lines));
        
        if ($exit_code !== 0 || empty($final_response) || 
            strpos($final_response, '❌') !== false || 
            strlen($final_response) < 10) { 
            
            error_log("Python AI failed (Exit: $exit_code). Full Output: \n{$raw_output}");
            
            if ($exit_code !== 0) {
                 return "Execution Error: Python script exited with code {$exit_code}. Raw output:\n{$raw_output}";
            }
            return false;
        }
        
        return $final_response;
    }
    
    public function handleAIChat($message) {
        $python_response = $this->runPythonAI('gemini_sql_agent.py', ['query', $message]);
        
        if ($python_response && strpos($python_response, 'Execution Error:') === false) {
            return [
                'success' => true,
                'response' => trim($python_response),
                'type' => 'ai_response'
            ];
        } else {
            $error_message = (strpos($python_response, 'Execution Error:') !== false) ? 
                             $python_response : 
                             '🤖 AI service is currently unavailable or returned an empty response. Check server logs.';
            return [
                'success' => false,
                'response' => $error_message,
                'type' => 'error'
            ];
        }
    }
    
    public function handleInterviewPrep($company) {
        $python_response = $this->runPythonAI('gemini_sql_agent.py', ['interview', $company]);
        
        if ($python_response && strpos($python_response, 'Execution Error:') === false) {
            return [
                'success' => true,
                'response' => trim($python_response),
                'type' => 'interview_prep'
            ];
        } else {
            $error_message = (strpos($python_response, 'Execution Error:') !== false) ? 
                             $python_response : 
                             "Could not generate preparation guide for $company (AI error).";
            return [
                'success' => false, 
                'response' => $error_message,
                'type' => 'error'
            ];
        }
    }
    
    public function getCompanies() {
        $conn_result = $this->getDBConnection();
        if (is_array($conn_result)) return ['success' => false, 'error' => $conn_result['message']];

        $result = $conn_result->query("SELECT DISTINCT company_name FROM placement ORDER BY company_name");
        $companies = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $companies[] = $row['company_name'];
            }
        }
        
        $conn_result->close();
        return ['success' => true, 'companies' => $companies];
    }
    
    public function testAISystem() {
        $conn_result = $this->getDBConnection();
        if (is_array($conn_result)) {
            return ['success' => false, 'db_message' => '❌ DB Error: ' . $conn_result['message'], 'ai_message' => 'Not Tested'];
        }
        $conn_result->close();

        $test_response = $this->runPythonAI('gemini_sql_agent.py', ['test', '']);
        
        if ($test_response && strlen($test_response) > 10 && strpos($test_response, 'Execution Error:') === false) {
            return ['success' => true, 'db_message' => '✅ Database connected', 'ai_message' => '✅ AI System working'];
        } else {
             $ai_message = (strpos($test_response, 'Execution Error:') !== false) ? 
                          $test_response : 
                          '❌ AI System failed. Check terminal/logs for full Python error.';
            return ['success' => false, 'db_message' => '✅ Database connected', 'ai_message' => $ai_message];
        }
    }
}

// Initialize system
$aiSystem = new AIChatSystem();

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // CRITICAL FIX STEP 2: Change the directory before running the Python script
    chdir($script_dir);
    
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'send_message':
            echo json_encode($aiSystem->handleAIChat($_POST['message']));
            break;
        case 'get_companies':
            echo json_encode($aiSystem->getCompanies());
            break;
        case 'get_interview_prep':
            echo json_encode($aiSystem->handleInterviewPrep($_POST['company']));
            break;
        case 'test_ai':
            echo json_encode($aiSystem->testAISystem());
            break;
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EEE Placement AI Assistant</title>
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
        
        /* Navbar Styles - Matching dashboard.php */
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
        
        /* Mobile Menu Overlay */
        .mobile-menu {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
        
        .mobile-menu-content {
            position: fixed;
            top: 0;
            right: -300px;
            width: 300px;
            height: 100%;
            background: #2c3e50;
            transition: right 0.3s ease;
            padding: 20px;
            overflow-y: auto;
        }
        
        .mobile-menu.active {
            display: block;
        }
        
        .mobile-menu.active .mobile-menu-content {
            right: 0;
        }
        
        .mobile-menu-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #34495e;
        }
        
        .mobile-menu-close {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
        }
        
        .mobile-nav-links {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .mobile-nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 12px 15px;
            border-radius: 4px;
            transition: background-color 0.3s;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .mobile-nav-links a:hover {
            background-color: #34495e;
        }
        
        .mobile-user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: #34495e;
            border-radius: 8px;
        }
        
        .mobile-user-info img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        /* Container */
        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 30px;
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
        
        .ai-badge {
            background: #27ae60;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            display: inline-block;
            margin-top: 10px;
        }
        
        /* Main Content */
        .app-container {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 25px;
            min-height: 70vh;
        }
        
        .sidebar {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            height: fit-content;
        }
        
        .sidebar h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 20px;
            border-bottom: 2px solid #ecf0f1;
            padding-bottom: 10px;
        }
        
        .main-content {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
        }
        
        .chat-container {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 20px;
            max-height: 550px;
            border: 1px solid #ecf0f1;
        }
        
        .message {
            margin-bottom: 20px;
            padding: 15px 20px;
            border-radius: 12px;
            max-width: 80%;
            animation: fadeIn 0.3s ease;
            white-space: pre-wrap;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .message.user {
            background: #3498db;
            color: white;
            margin-left: auto;
            border-bottom-right-radius: 5px;
        }
        
        .message.assistant {
            background: white;
            border: 2px solid #ecf0f1;
            border-bottom-left-radius: 5px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .message.assistant.ai-response {
            border-left: 4px solid #3498db;
        }
        
        .chat-input {
            display: flex;
            gap: 12px;
        }
        
        .chat-input input {
            flex: 1;
            padding: 15px 20px;
            border: 2px solid #ecf0f1;
            border-radius: 8px;
            font-size: 16px;
            outline: none;
            transition: border-color 0.3s;
        }
        
        .chat-input input:focus {
            border-color: #3498db;
        }
        
        .chat-input button {
            padding: 15px 30px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s;
        }
        
        .chat-input button:hover:not(:disabled) {
            background: #2980b9;
        }

        .chat-input button:disabled {
            background: #bdc3c7;
            cursor: not-allowed;
        }
        
        .examples-grid {
            display: grid;
            gap: 10px;
            margin: 20px 0;
        }
        
        .example-btn {
            padding: 12px 15px;
            background: #f8f9fa;
            border: 1px solid #ecf0f1;
            border-radius: 8px;
            cursor: pointer;
            text-align: left;
            transition: all 0.3s;
            border-left: 4px solid #3498db;
            color: #2c3e50;
            font-size: 14px;
        }
        
        .example-btn:hover {
            background: #3498db;
            color: white;
            transform: translateX(5px);
        }
        
        .ai-thinking {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #3498db;
            font-weight: 600;
        }
        
        .ai-dots {
            display: inline-flex;
            gap: 3px;
        }
        
        .ai-dots span {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #3498db;
            animation: bounce 1.4s ease-in-out infinite both;
        }
        
        .ai-dots span:nth-child(1) { animation-delay: -0.32s; }
        .ai-dots span:nth-child(2) { animation-delay: -0.16s; }
        
        @keyframes bounce {
            0%, 80%, 100% { transform: scale(0); }
            40% { transform: scale(1); }
        }
        
        .system-status {
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #27ae60;
        }
        
        .system-status h4 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .tab-navigation {
            display: flex;
            margin-bottom: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            padding: 5px;
        }
        
        .tab-btn {
            flex: 1;
            padding: 12px;
            border: none;
            background: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
            color: #7f8c8d;
        }
        
        .tab-btn.active {
            background: #3498db;
            color: white;
        }
        
        .tab-content {
            display: none;
            flex: 1;
        }
        
        .tab-content.active {
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .status-success { color: #27ae60; font-weight: bold; }
        .status-error { color: #e74c3c; font-weight: bold; }
        
        /* Footer */
        footer {
            background-color: #2c3e50;
            color: white;
            text-align: center;
            padding: 20px;
            margin-top: 40px;
        }
        
        /* Responsive Design */
        @media (max-width: 1024px) {
            .app-container {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                order: 2;
            }
            
            .main-content {
                order: 1;
            }
        }
        
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
                margin: 20px auto;
            }
            
            .page-header h1 {
                font-size: 24px;
            }
            
            .page-header p {
                font-size: 16px;
            }
            
            .chat-messages {
                max-height: 400px;
            }
            
            .message {
                max-width: 90%;
            }
        }
        
        @media (max-width: 480px) {
            .navbar {
                padding: 12px 15px;
            }
            
            .logo {
                height: 40px;
            }
            
            .nav-title {
                font-size: 18px;
            }
            
            .mobile-menu-content {
                width: 280px;
            }
            
            .chat-input {
                flex-direction: column;
            }
            
            .chat-input button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar - Matching dashboard.php -->
    <nav class="navbar">
        <div class="logo-container">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/4/43/Itechlogo.png/738px-Itechlogo.png" alt="PSG iTech Logo" class="logo">
            <span class="nav-title">EEE Department</span>
        </div>
        <div class="nav-links">
            <a href="placement_experience.php"><i class="fas fa-book"></i> PLACED EXPERIENCE</a>
            <a href="dashboard.php"><i class="fas fa-pencil-alt"></i> PREP TOPICS</a>
            <?php if (in_array($currentUserType, ['admin', 'faculty'])): ?>
                <a href="admin_panel.php"><i class="fas fa-user-shield"></i> Admin Panel</a>
            <?php endif; ?>
            <a href="logout.php" style="background: #e74c3c;"><i class="fas fa-sign-out-alt"></i> LOGOUT</a>
        </div>
        <div class="user-info">
            <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="User">
            <span><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></span>
            <small style="font-size: 11px; opacity: 0.8;">(<?php echo ucfirst($currentUserType); ?>)</small>
        </div>
        <button class="mobile-menu-btn" id="mobileMenuBtn">
            <i class="fas fa-bars"></i>
        </button>
    </nav>

    <!-- Mobile Menu -->
    <div class="mobile-menu" id="mobileMenu">
        <div class="mobile-menu-content">
            <div class="mobile-menu-header">
                <h3>Menu</h3>
                <button class="mobile-menu-close" id="mobileMenuClose">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="mobile-nav-links">
                <a href="placement_experience.php"><i class="fas fa-book"></i> PLACED EXPERIENCE</a>
                <a href="dashboard.php"><i class="fas fa-pencil-alt"></i> PREP TOPICS</a>
                <?php if (in_array($currentUserType, ['admin', 'faculty'])): ?>
                    <a href="admin_panel.php"><i class="fas fa-user-shield"></i> Admin Panel</a>
                <?php endif; ?>
                <a href="logout.php" style="background: #e74c3c;"><i class="fas fa-sign-out-alt"></i> LOGOUT</a>
            </div>
            <div class="mobile-user-info">
                <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="User">
                <div>
                    <div style="font-weight: bold;"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></div>
                    <div style="font-size: 14px; color: #bdc3c7;"><?php echo ucfirst($currentUserType); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <div class="page-header">
            <h1>🎓 EEE Placement AI Assistant</h1>
        </div>
        
        <div class="app-container">
            <div class="sidebar">
                <h3>🚀 AI Quick Questions</h3>
                <div class="examples-grid">
                    <button class="example-btn" onclick="sendExample('Which students got the highest packages and why?')">
                        🏆 Top Packages Analysis
                    </button>
                    <button class="example-btn" onclick="sendExample('What should I study to get placed in Google?')">
                        🎯 Google Preparation
                    </button>
                    <button class="example-btn" onclick="sendExample('Analyze placement trends for EEE students')">
                        📈 Trends Analysis
                    </button>
                    <button class="example-btn" onclick="sendExample('Compare packages between dream and super dream companies')">
                        ⚖️ Company Comparison
                    </button>
                    <button class="example-btn" onclick="sendExample('What are the most common technical rounds?')">
                        🔄 Interview Rounds
                    </button>
                    <button class="example-btn" onclick="sendExample('Create a 6-month placement preparation plan')">
                        📅 Study Plan
                    </button>
                </div>
                
                <div class="system-status">
                    <h4>🔧 System Status</h4>
                    <div id="db-status" class="status-error">Checking database...</div>
                    <div id="ai-status" class="status-error" style="margin-top: 10px;">Checking AI...</div>
                    <button onclick="testAI()" style="margin-top: 10px; width: 100%; padding: 10px; background: #3498db; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
                        Test AI System
                    </button>
                </div>
            </div>
            
            <div class="main-content">
                <div class="tab-navigation">
                    <button class="tab-btn active" onclick="switchTab('chat', event)">💬 AI Chat</button>
                    <button class="tab-btn" onclick="switchTab('interview', event)">🎯 Interview Prep</button>
                </div>
                
                <div id="chat-tab" class="tab-content active">
                    <div class="chat-container">
                        <div class="chat-messages" id="chat-messages">
                            <div class="message assistant ai-response">
                                <strong>🤖 AI Assistant:</strong> Welcome! I'm your AI-powered placement assistant. I can analyze placement data, provide study guidance, interview preparation, and insights. What would you like to know?
                            </div>
                        </div>
                        
                        <div class="chat-input">
                            <input type="text" id="message-input" placeholder="Ask anything about placements, preparation, or analysis..." onkeypress="handleKeyPress(event)">
                            <button onclick="sendMessage()">
                                Send 
                                <span class="ai-thinking" style="display: none;" id="send-thinking">
                                    <span class="ai-dots">
                                        <span></span>
                                        <span></span>
                                        <span></span>
                                    </span>
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div id="interview-tab" class="tab-content">
                    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px; flex: 1;">
                        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; display: flex; flex-direction: column; border: 1px solid #ecf0f1;">
                            <h4 style="color: #2c3e50; margin-bottom: 15px;">🏢 Select Company</h4>
                            <div id="company-list" style="margin-top: 15px; overflow-y: auto; flex: 1;">
                                <div class="ai-thinking">
                                    <span class="ai-dots"><span></span><span></span><span></span></span>
                                    Loading companies...
                                </div>
                            </div>
                        </div>
                        
                        <div style="background: white; padding: 20px; border-radius: 8px; border: 2px solid #ecf0f1; overflow-y: auto;">
                            <h4 style="color: #2c3e50; margin-bottom: 15px;">🎯 AI-Powered Interview Preparation</h4>
                            <div id="preparation-content">
                                <p style="color: #7f8c8d;">Select a company from the list to get a detailed, AI-generated personalized interview preparation guide based on available placement data.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <p>Department of EEE - PSG iTech © 2025. All rights reserved.</p>
    </footer>

    <script>
        // Mobile menu functionality
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const mobileMenu = document.getElementById('mobileMenu');
            const mobileMenuClose = document.getElementById('mobileMenuClose');

            if (mobileMenuBtn) {
                mobileMenuBtn.addEventListener('click', function() {
                    mobileMenu.classList.add('active');
                    document.body.style.overflow = 'hidden';
                });
            }

            if (mobileMenuClose) {
                mobileMenuClose.addEventListener('click', function() {
                    mobileMenu.classList.remove('active');
                    document.body.style.overflow = '';
                });
            }

            // Close menu when clicking overlay
            if (mobileMenu) {
                mobileMenu.addEventListener('click', function(e) {
                    if (e.target === mobileMenu) {
                        mobileMenu.classList.remove('active');
                        document.body.style.overflow = '';
                    }
                });
            }

            // Test system status on page load
            testAI();
        });

        // AI Chat System JavaScript
        let currentTab = 'chat';
        
        function switchTab(tabName, event) {
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.getElementById(tabName + '-tab').classList.add('active');
            event.target.classList.add('active');
            currentTab = tabName;
            
            if (tabName === 'interview') {
                loadCompanies();
            }
        }
        
        function sendMessage() {
            const input = document.getElementById('message-input');
            const message = input.value.trim();
            const sendBtn = document.querySelector('.chat-input button');
            const thinkingIndicator = document.getElementById('send-thinking');
            
            if (message) {
                addMessage(message, 'user');
                input.value = '';
                
                // Show AI thinking
                sendBtn.disabled = true;
                thinkingIndicator.style.display = 'inline-flex';
                
                const thinkingId = addMessage(
                    '<div class="ai-thinking">' +
                        '<span class="ai-dots"><span></span><span></span><span></span></span>' +
                        'AI is analyzing your question...' +
                    '</div>', 
                    'assistant', 
                    true
                );
                
                // Send to AI
                fetch('', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'action=send_message&message=' + encodeURIComponent(message)
                })
                .then(response => response.json())
                .then(data => {
                    sendBtn.disabled = false;
                    thinkingIndicator.style.display = 'none';
                    
                    const thinkingElem = document.getElementById(thinkingId);
                    if (thinkingElem) thinkingElem.remove();
                    
                    if (data.success) {
                        addMessage(data.response, 'assistant', false, 'ai-response');
                    } else {
                        addMessage('❌ ' + data.response, 'assistant', false, 'error-response');
                        
                        if (data.response.includes("Execution Error")) {
                           addMessage('<pre style="color:red; background:#ffeeee; padding: 10px; border-radius: 5px;">' + data.response + '</pre>', 'assistant', false, 'error-response');
                        }
                    }
                })
                .catch(error => {
                    sendBtn.disabled = false;
                    thinkingIndicator.style.display = 'none';
                    
                    const thinkingElem = document.getElementById(thinkingId);
                    if (thinkingElem) thinkingElem.remove();
                    
                    addMessage('❌ Network or Server error. AI service unavailable.', 'assistant', false, 'error-response');
                });
            }
        }
        
        function sendExample(message) {
            document.getElementById('message-input').value = message;
            sendMessage();
        }
        
        function addMessage(content, role, isTemp = false, extraClass = '') {
            const messagesContainer = document.getElementById('chat-messages');
            const messageId = 'msg-' + Date.now();
            
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${role} ${extraClass}`;
            messageDiv.id = isTemp ? messageId : '';
            
            const prefix = role === 'user' ? '👤 You:' : '🤖 AI:';
            messageDiv.innerHTML = `<strong>${prefix}</strong> ${content}`;
            
            messagesContainer.appendChild(messageDiv);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
            
            return messageId;
        }
        
        function handleKeyPress(event) {
            if (event.key === 'Enter') sendMessage();
        }
        
        function testAI() {
            const dbStatus = document.getElementById('db-status');
            const aiStatus = document.getElementById('ai-status');
            
            if (dbStatus) dbStatus.innerHTML = '<div class="ai-thinking">Testing Database...</div>';
            if (aiStatus) aiStatus.innerHTML = '<div class="ai-thinking">Testing AI System...</div>';
            
            fetch('', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=test_ai'
            })
            .then(response => response.json())
            .then(data => {
                if (dbStatus) {
                    dbStatus.innerHTML = data.db_message;
                    dbStatus.className = data.db_message.startsWith('✅') ? 'status-success' : 'status-error';
                }

                if (aiStatus) {
                    aiStatus.innerHTML = data.ai_message;
                    aiStatus.className = data.ai_message.startsWith('✅') ? 'status-success' : 'status-error';
                }
                
                if (data.ai_message.includes("Execution Error")) {
                    const chatMessages = document.getElementById('chat-messages');
                    if (chatMessages) {
                        chatMessages.innerHTML += `<div class="message assistant error-response"><strong>🤖 AI:</strong> <pre style="color:red; background:#ffeeee; padding: 10px; border-radius: 5px;">${data.ai_message}</pre></div>`;
                    }
                }
            })
            .catch(error => {
                console.error('Test AI Error:', error);
                if (dbStatus) {
                    dbStatus.innerHTML = '❌ Connection failed';
                    dbStatus.className = 'status-error';
                }
                if (aiStatus) {
                    aiStatus.innerHTML = '❌ Test failed';
                    aiStatus.className = 'status-error';
                }
            });
        }
        
        function loadCompanies() {
            const companyList = document.getElementById('company-list');
            
            if (companyList) {
                companyList.innerHTML = '<div class="ai-thinking"><span class="ai-dots"><span></span><span></span><span></span></span> Loading companies...</div>';
                
                fetch('', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'action=get_companies'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.companies.length > 0) {
                        let html = '';
                        data.companies.forEach(company => {
                            const safeCompanyName = company.replace(/'/g, "\\'"); 
                            html += `<button class="example-btn" onclick="getInterviewPrep('${safeCompanyName}')" style="margin: 5px 0; width: 100%;">${company}</button>`;
                        });
                        companyList.innerHTML = html;
                    } else if (data.success && data.companies.length === 0) {
                         companyList.innerHTML = '<div class="status-error">No companies found in the placement table.</div>';
                    } else {
                        companyList.innerHTML = `<div class="status-error">❌ ${data.error || 'Failed to connect to database for company list.'}</div>`;
                    }
                })
                .catch(error => {
                    console.error('Load Companies Error:', error);
                    companyList.innerHTML = '<div class="status-error">❌ Failed to load companies</div>';
                });
            }
        }
        
        function getInterviewPrep(companyName) {
            const preparationContent = document.getElementById('preparation-content');
            
            if (preparationContent) {
                preparationContent.innerHTML = 
                    '<div class="ai-thinking">' +
                        '<span class="ai-dots"><span></span><span></span><span></span></span>' +
                        'AI is generating personalized preparation guide for ' + companyName +
                    '</div>';
                
                fetch('', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'action=get_interview_prep&company=' + encodeURIComponent(companyName)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        preparationContent.innerHTML = `
                            <h3 style="color: #2c3e50; margin-bottom: 15px;">🎯 AI-Powered Preparation for ${companyName}</h3>
                            <div style="margin-top: 15px; white-space: pre-wrap; line-height: 1.6; background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #3498db;">
                                ${data.response}
                            </div>
                        `;
                    } else {
                        preparationContent.innerHTML = '<div class="status-error">❌ ' + data.response + '</div>';
                    }
                })
                .catch(error => {
                    console.error('Interview Prep Error:', error);
                    preparationContent.innerHTML = '<div class="status-error">❌ Failed to generate preparation guide</div>';
                });
            }
        }
    </script>
</body>
</html>