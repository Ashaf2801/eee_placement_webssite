<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['mail_id']) || !isset($_SESSION['user_type'])) {
    header('Location: index.html');
    exit();
}

// Get user information
$currentUserMail = $_SESSION['mail_id'];
$currentUserType = $_SESSION['user_type'];
$username = $_SESSION['user_name'] ?? 'User';
$firstLetter = strtoupper(substr($username, 0, 1));

// Flask API Configuration
define('FLASK_API_URL', 'http://127.0.0.1:5001');

class AIChatSystem {
    private function callFlaskAPI($endpoint, $data = [], $method = 'POST') {
        $url = FLASK_API_URL . $endpoint;
        $ch = curl_init($url);

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-User-ID: ' . $_SESSION['mail_id'],
                'X-User-Type: ' . $_SESSION['user_type']
            ]
        ];

        if ($method === 'POST') {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        }

        curl_setopt_array($ch, $options);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return [
                'success' => false,
                'error' => 'cURL error: ' . $error
            ];
        }

        curl_close($ch);

        if ($httpCode !== 200) {
            return [
                'success' => false,
                'error' => 'Flask API error: HTTP ' . $httpCode . '. Make sure Flask server is running on port 5000.'
            ];
        }

        $decoded = json_decode($response, true);
        if ($decoded === null) {
            return [
                'success' => false,
                'error' => 'Invalid JSON response from Flask'
            ];
        }

        return $decoded;
    }

    public function handleAIChat($message) {
        return $this->callFlaskAPI('/api/chat', ['message' => $message]);
    }

    public function handleInterviewPrep($company) {
        return $this->callFlaskAPI('/api/interview-prep', ['company' => $company]);
    }

    public function getCompanies() {
        return $this->callFlaskAPI('/api/companies', [], 'GET');
    }

    public function testAISystem() {
        return $this->callFlaskAPI('/api/test', [], 'GET');
    }
}

// Initialize system
$aiSystem = new AIChatSystem();

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && 
    (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false || 
     isset($_SERVER['HTTP_CONTENT_TYPE']) && strpos($_SERVER['HTTP_CONTENT_TYPE'], 'application/json') !== false)) {
    
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true);

    if (isset($input['action'])) {
        // Old-style action-based routing
        switch ($input['action']) {
            case 'send_message':
                echo json_encode($aiSystem->handleAIChat($input['message']));
                break;
            case 'get_interview_prep':
                echo json_encode($aiSystem->handleInterviewPrep($input['company']));
                break;
            default:
                echo json_encode(['success' => false, 'error' => 'Invalid action']);
        }
    } elseif (isset($input['message'])) {
        // Direct message
        echo json_encode($aiSystem->handleAIChat($input['message']));
    } elseif (isset($input['company'])) {
        // Direct company
        echo json_encode($aiSystem->handleInterviewPrep($input['company']));
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid POST payload']);
    }
    exit;
}

// Handle GET requests with query parameters
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['action']) {
        case 'companies':
            echo json_encode($aiSystem->getCompanies());
            break;
        case 'test':
            echo json_encode($aiSystem->testAISystem());
            break;
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid GET action']);
    }
    exit;
}

// If not an AJAX/API call, render the HTML frontend
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

        .nav-links a:hover {
            background-color: #34495e;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .nav-links a:active {
            transform: translateY(0);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 16px;
            background: #2c3e50;
            border-radius: 8px;
            transition: all 0.3s ease;
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

        /* Mobile Menu Button */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
            padding: 8px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .mobile-menu-btn:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        /* Mobile Menu Overlay */
        .mobile-menu {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
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
            padding: 0;
            overflow-y: auto;
            box-shadow: -5px 0 30px rgba(0, 0, 0, 0.3);
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
            padding: 0.1px 10px 0.1px;
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
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .mobile-menu-close:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: rotate(90deg);
        }

        .mobile-nav-links {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 20px;
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

        .mobile-nav-links a:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(8px);
        }

        .mobile-user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border-radius: 10px;
            margin: 20px 25px;
        }

        .mobile-logout-link {
            background: linear-gradient(135deg, #0080ffff 0%, #006effff 100%) !important;
            margin-top: 10px;
        }

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
        
        .ai-badge {
            background: #27ae60;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            display: inline-block;
            margin-top: 10px;
        }
        
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
        
        footer {
            background-color: #2c3e50;
            color: white;
            text-align: center;
            padding: 20px;
            margin-top: 40px;
        }
        
        @media (max-width: 1200px) {
            .nav-links, .user-info {
                display: none;
            }
            .mobile-menu-btn {
                display: block;
            }
        }
        
        @media (max-width: 1024px) {
            .app-container {
                grid-template-columns: 1fr;
            }

            .sidebar {
                order: 1; /* Show sidebar first on mobile */
            }
            
            .main-content {
                order: 2; /* Show main content after sidebar */
            }
        }
        
        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 26px;
            }

            .sidebar h3 {
                font-size: 18px;
            }

            .example-btn {
                padding: 10px 12px;
                font-size: 13px;
            }

            .chat-input input {
                padding: 12px 15px;
                font-size: 14px;
            }

            .chat-input button {
                padding: 12px 20px;
            }

            .chat-messages {
                max-height: 400px;
            }
            
            .message {
                max-width: 90%;
                font-size: 14px;
                padding: 12px 15px;
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
    <nav class="navbar">
        <div class="logo-container">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/4/43/Itechlogo.png/738px-Itechlogo.png" alt="PSG iTech Logo" class="logo">
            <span class="nav-title">EEE Department</span>
        </div>
        
        <div class="nav-content">
            <div class="nav-links">
                <a href="dashboard.php"><i class="fas fa-home"></i> HOME</a>
                <a href="placement_experience.php"><i class="fas fa-book"></i> PLACED EXPERIENCE</a>
                <a href="chatbot.php" style="background: #34495e;"><i class="fas fa-pencil-alt"></i> PREP WITH AI</a>
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

    <!-- Mobile Menu -->
    <div class="mobile-menu" id="mobileMenu">
        <div class="mobile-menu-content">
            <div class="mobile-menu-header">
                <div class="mobile-user-info">
                <div>
                    <div style="font-weight: 700; font-size: 16px; color: white; margin-bottom: 2px;"><?php echo htmlspecialchars($username); ?>
                    <div style="font-size: 13px; color: #bdc3c7; background: rgba(255, 255, 255, 0.1); padding: 1px 10px; border-radius: 20px; display: inline-block; font-weight: 500;"><?php echo ucfirst($currentUserType); ?></div></div>
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
            <h1>🎓 EEE Placement AI Assistant</h1>
        </div>
        
        <div class="app-container">
            <div class="sidebar">
                <div id="interview-prep-sidebar">
                    <h3 style="color: #2c3e50; margin-bottom: 15px;">🎯 Interview Prep</h3>
                    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #ecf0f1; margin-bottom: 20px;">
                        <h4 style="color: #2c3e50; margin-bottom: 15px;">🏢 Select Company</h4>
                        <div id="company-list" style="margin-top: 15px; overflow-y: auto; max-height: 250px;">
                            <div class="ai-thinking">
                                <span class="ai-dots"><span></span><span></span><span></span></span>
                                Loading...
                            </div>
                        </div>
                    </div>
                </div>

                <div class="system-status">
                    <h4>🔧 System Status</h4>
                    <div id="db-status" class="status-error">Checking...</div>
                    <div id="ai-status" class="status-error" style="margin-top: 10px;">Checking...</div>
                    <button onclick="testSystem()" style="margin-top: 10px; width: 100%; padding: 10px; background: #3498db; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
                        Test System
                    </button>
                </div>
            </div>
            
            <div class="main-content">
                <div class="chat-container">
                    <div class="chat-messages" id="chat-messages">
                        <div class="message assistant">Welcome! I'm your AI-powered placement assistant. I can analyze placement data, provide study guidance, and interview preparation. What would you like to know?</div>
                    </div>
                    
                    <div class="chat-input">
                        <input type="text" id="message-input" placeholder="Ask anything about placements..." onkeypress="handleKeyPress(event)">
                        <button onclick="sendMessage()" id="send-btn">
                            Send
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function sendMessage() {
            const input = document.getElementById('message-input');
            const message = input.value.trim();
            const sendBtn = document.getElementById('send-btn');
            
            if (!message) return;
            
            addMessage(message, 'user');
            input.value = '';
            
            sendBtn.disabled = true;
            sendBtn.innerHTML = '<span class="ai-thinking"><span class="ai-dots"><span></span><span></span><span></span></span></span>';
            
            const thinkingId = addMessage(
                '<div class="ai-thinking"><span class="ai-dots"><span></span><span></span><span></span></span> AI is analyzing...</div>', 
                'assistant', 
                true
            );
            
            // FIXED: Call chatbot.php instead of /api/chat
            fetch('chatbot.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({message: message})
            })
            .then(response => response.json())
            .then(data => {
                sendBtn.disabled = false;
                sendBtn.innerHTML = 'Send';
                
                const thinkingElem = document.getElementById(thinkingId);
                if (thinkingElem) thinkingElem.remove();
                
                if (data.success) {
                    addMessage(data.response, 'assistant');
                } else {
                    addMessage('❌ ' + (data.error || 'An error occurred'), 'assistant');
                }
            })
            .catch(error => {
                sendBtn.disabled = false;
                sendBtn.innerHTML = 'Send';
                
                const thinkingElem = document.getElementById(thinkingId);
                if (thinkingElem) thinkingElem.remove();
                
                addMessage('❌ Network error: ' + error.message, 'assistant');
            });
        }
        
        function sendExample(message) {
            document.getElementById('message-input').value = message;
            sendMessage();
        }
        
        function addMessage(content, role, isTemp = false) {
            const messagesContainer = document.getElementById('chat-messages');
            const messageId = 'msg-' + Date.now();
            
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${role}`;
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
        
        function testSystem() {
            const dbStatus = document.getElementById('db-status');
            const aiStatus = document.getElementById('ai-status');
            
            dbStatus.innerHTML = '<div class="ai-thinking">Testing...</div>';
            aiStatus.innerHTML = '<div class="ai-thinking">Testing...</div>';
            
            // FIXED: Call chatbot.php?action=test
            fetch('chatbot.php?action=test')
            .then(response => response.json())
            .then(data => {
                dbStatus.innerHTML = data.db_status || '❌ No status';
                dbStatus.className = (data.db_status && data.db_status.startsWith('✅')) ? 'status-success' : 'status-error';
                
                aiStatus.innerHTML = data.ai_status || '❌ No status';
                aiStatus.className = (data.ai_status && data.ai_status.startsWith('✅')) ? 'status-success' : 'status-error';
            })
            .catch(error => {
                dbStatus.innerHTML = '❌ Test failed: ' + error.message;
                dbStatus.className = 'status-error';
                aiStatus.innerHTML = '❌ Test failed';
                aiStatus.className = 'status-error';
            });
        }
        
        function loadCompanies() {
            const companyList = document.getElementById('company-list');
            companyList.innerHTML = '<div class="ai-thinking"><span class="ai-dots"><span></span><span></span><span></span></span> Loading...</div>';
            
            // FIXED: Call chatbot.php?action=companies
            fetch('chatbot.php?action=companies')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.companies && data.companies.length > 0) {
                    let html = '';
                    data.companies.forEach(company => {
                        html += `<button class="example-btn" onclick="getInterviewPrep('${company.replace(/'/g, "\\'")}')" style="margin: 5px 0; width: 100%;">${company}</button>`;
                    });
                    companyList.innerHTML = html;
                } else {
                    companyList.innerHTML = '<div class="status-error">No companies found' + (data.error ? ': ' + data.error : '') + '</div>';
                }
            })
            .catch(error => {
                companyList.innerHTML = '<div class="status-error">❌ Failed to load: ' + error.message + '</div>';
            });
        }
        
        function getInterviewPrep(companyName) {
            const userMessage = `Tell me about interview preparation for ${companyName}`;
            addMessage(userMessage, 'user');

            // On mobile, scroll to the chat section after clicking a company
            if (window.innerWidth <= 1024) {
                const mainContent = document.querySelector('.main-content');
                if (mainContent) {
                    mainContent.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
            
            const thinkingId = addMessage(
                `<div class="ai-thinking"><span class="ai-dots"><span></span><span></span><span></span></span> Generating guide for ${companyName}...</div>`, 
                'assistant', 
                true
            );
            
            fetch('chatbot.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({company: companyName})
            })
            .then(response => response.json())
            .then(data => {
                const thinkingElem = document.getElementById(thinkingId);
                if (thinkingElem) thinkingElem.remove();

                if (data.success) {
                    addMessage(data.response, 'assistant');
                } else {
                    addMessage('❌ ' + (data.error || 'Failed to generate guide'), 'assistant');
                }
            })
            .catch(error => {
                const thinkingElem = document.getElementById(thinkingId);
                if (thinkingElem) thinkingElem.remove();
                addMessage('❌ Network error: ' + error.message, 'assistant');
            });
        }
        
        // Test system on load
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile menu functionality
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

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && mobileMenu && mobileMenu.classList.contains('active')) {
                    mobileMenu.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });

            testSystem();
            loadCompanies();
        });
    </script>
</body>
</html>