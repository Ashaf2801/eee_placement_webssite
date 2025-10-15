<?php
// chatbot.php - AI-POWERED CHAT INTERFACE
session_start();

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
        // REPLACE 'AIzaSyDqjHK3PUeV8hlYkCr-N4xAQwYw7gmP20Q' with your ACTUAL API key.
        $this->gemini_api_key = 'AIzaSyDqjHK3PUeV8hlYkCr-N4xAQwYw7gmP20Q'; // ⬅️ **ENSURE THIS IS CORRECT**
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
    
    /**
     * Executes the Python AI script, injects the API key, and isolates the final AI response.
     * FIX: Uses Windows 'set' command syntax for environment variables.
     */
    public function runPythonAI($script_path, $args = []) {
        // Pass Gemini API key and DB credentials using Windows 'set' syntax
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

        // Pipe stderr to stdout (2>&1) to capture errors and logs
        $full_command .= " 2>&1"; 

        $output_lines = [];
        $exit_code = 0;
        
        // Execute the command
        exec($full_command, $output_lines, $exit_code);
        
        $raw_output = implode("\n", $output_lines);

        // --- FIX: ISOLATE THE RESPONSE ---
        $cleaned_lines = [];
        $start_capturing = false;
        
        foreach($output_lines as $line) {
            // Look for the last clear log indicator before the AI response starts
            if (strpos($line, '🤖 Analyzing results with AI...') !== false) {
                $start_capturing = true; 
                continue;
            }
            
            // Start capturing only after the final log line
            if ($start_capturing) {
                 // Skip any warning/error log lines that might follow the main content start
                 if (preg_match('/^(WARNING|E\d{4})/', trim($line))) {
                     continue; 
                 }
                $cleaned_lines[] = $line;
            }
        }
        
        $final_response = trim(implode("\n", $cleaned_lines));
        
        // 4. Check for critical failures
        if ($exit_code !== 0 || empty($final_response) || 
            strpos($final_response, '❌') !== false || 
            strlen($final_response) < 10) { 
            
            error_log("Python AI failed (Exit: $exit_code). Full Output: \n{$raw_output}");
            
            if ($exit_code !== 0) {
                 // Return the raw output for debugging
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
    <style>
        :root {
            --primary: #1a73e8;
            --secondary: #34a853;
            --accent: #ea4335;
            --dark: #202124;
            --light: #f8f9fa;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, var(--primary), #0d47a1);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .ai-badge {
            background: var(--secondary);
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            display: inline-block;
            margin-top: 10px;
        }
        
        .app-container {
            display: grid;
            grid-template-columns: 300px 1fr;
            min-height: 70vh;
        }
        
        .sidebar {
            background: var(--light);
            padding: 25px;
            border-right: 1px solid #e0e0e0;
        }
        
        .main-content {
            padding: 25px;
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
            background: #fafafa;
            border-radius: 10px;
            margin-bottom: 20px;
            max-height: 500px; 
        }
        
        .message {
            margin-bottom: 20px;
            padding: 15px 20px;
            border-radius: 15px;
            max-width: 80%;
            animation: fadeIn 0.3s ease;
            white-space: pre-wrap; 
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .message.user {
            background: var(--primary);
            color: white;
            margin-left: auto;
            border-bottom-right-radius: 5px;
        }
        
        .message.assistant {
            background: white;
            border: 2px solid #e0e0e0;
            border-bottom-left-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .message.assistant.ai-response {
            border-left: 4px solid var(--primary);
        }
        
        .chat-input {
            display: flex;
            gap: 12px;
        }
        
        .chat-input input {
            flex: 1;
            padding: 15px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            font-size: 16px;
            outline: none;
            transition: border-color 0.3s;
        }
        
        .chat-input input:focus {
            border-color: var(--primary);
        }
        
        .chat-input button {
            padding: 15px 30px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s;
        }
        
        .chat-input button:hover:not(:disabled) {
            background: #0d47a1;
        }

        .chat-input button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        .examples-grid {
            display: grid;
            gap: 10px;
            margin: 20px 0;
        }
        
        .example-btn {
            padding: 12px 15px;
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            cursor: pointer;
            text-align: left;
            transition: all 0.3s;
            border-left: 4px solid var(--primary);
        }
        
        .example-btn:hover {
            background: var(--primary);
            color: white;
            transform: translateX(5px);
        }
        
        .ai-thinking {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--primary);
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
            background: var(--primary);
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
            background: white;
            border-radius: 8px;
            border-left: 4px solid var(--secondary);
        }
        
        .tab-navigation {
            display: flex;
            margin-bottom: 20px;
            background: var(--light);
            border-radius: 10px;
            padding: 5px;
        }
        
        .tab-btn {
            flex: 1;
            padding: 12px;
            border: none;
            background: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
        }
        
        .tab-btn.active {
            background: var(--primary);
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

        .status-success { color: var(--secondary); font-weight: bold; }
        .status-error { color: var(--accent); font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎓 EEE Placement AI Assistant</h1>
            <p>Powered by Google Gemini - Intelligent Placement Analytics</p>
            <div class="ai-badge">🤖 FULL AI-POWERED</div>
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
                    <button onclick="testAI()" style="margin-top: 10px; width: 100%; padding: 10px; background: var(--primary); color: white; border: none; border-radius: 5px; cursor: pointer;">
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
                                    AI Thinking
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div id="interview-tab" class="tab-content">
                    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px; flex: 1;">
                        <div style="background: var(--light); padding: 20px; border-radius: 10px; display: flex; flex-direction: column;">
                            <h4>🏢 Select Company</h4>
                            <div id="company-list" style="margin-top: 15px; overflow-y: auto; flex: 1;">
                                <div class="ai-thinking">
                                    <span class="ai-dots"><span></span><span></span><span></span></span>
                                    Loading companies...
                                </div>
                            </div>
                        </div>
                        
                        <div style="background: white; padding: 20px; border-radius: 10px; border: 2px solid var(--light); overflow-y: auto;">
                            <h4>🎯 AI-Powered Interview Preparation</h4>
                            <div id="preparation-content">
                                <p>Select a company from the list to get a detailed, AI-generated personalized interview preparation guide based on available placement data.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
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
                        // Display the error message from PHP/Python
                        addMessage('❌ ' + data.response, 'assistant', false, 'error-response');
                        
                        // New: Display the full Python output on the front-end for immediate debugging
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
            aiStatus.innerHTML = '<div class="ai-thinking">Testing AI System...</div>';
            
            fetch('', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=test_ai'
            })
            .then(response => response.json())
            .then(data => {
                dbStatus.innerHTML = data.db_message;
                dbStatus.className = data.db_message.startsWith('✅') ? 'status-success' : 'status-error';

                aiStatus.innerHTML = data.ai_message;
                aiStatus.className = data.ai_message.startsWith('✅') ? 'status-success' : 'status-error';
                
                // New: Display the full Python output on the front-end for immediate debugging
                if (data.ai_message.includes("Execution Error")) {
                    document.getElementById('chat-messages').innerHTML += `<div class="message assistant error-response"><strong>🤖 AI:</strong> <pre style="color:red; background:#ffeeee; padding: 10px; border-radius: 5px;">${data.ai_message}</pre></div>`;
                }
            });
        }
        
        function loadCompanies() {
            const companyList = document.getElementById('company-list');
            
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
            });
        }
        
        function getInterviewPrep(companyName) {
            const preparationContent = document.getElementById('preparation-content');
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
                        <h3>🎯 AI-Powered Preparation for ${companyName}</h3>
                        <div style="margin-top: 15px; white-space: pre-wrap; line-height: 1.6; background: #f8f9fa; padding: 20px; border-radius: 10px; border-left: 4px solid var(--primary);">
                            ${data.response}
                        </div>
                    `;
                } else {
                    preparationContent.innerHTML = '<div class="status-error">❌ ' + data.response + '</div>';
                }
            });
        }
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Test system status on page load
            testAI(); 
        });
    </script>
</body>
</html>