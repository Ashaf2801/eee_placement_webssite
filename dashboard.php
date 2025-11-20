<?php
session_start();

// Check if user is logged in - using mail_id instead of user_id
if (!isset($_SESSION['mail_id']) || !isset($_SESSION['user_type'])) {
    header('Location: index.html');
    exit();
}

// Get user information
$currentUserMail = $_SESSION['mail_id'];
$currentUserType = $_SESSION['user_type'];
$username = $_SESSION['user_name'] ?? 'User';
$firstLetter = strtoupper(substr($username, 0, 1));

// If you have a separate db_user_type, adjust accordingly
// $currentUserType = $_SESSION['db_user_type'] ?? $_SESSION['user_type'];
$currentYear = date('Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Placement Preparation - EEE Department</title>
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

        .mobile-menu-header h3 {
            color: white;
            font-size: 2px;
            font-weight: 600;
            margin: 0;
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
            position: relative;
            overflow: hidden;
        }

        .mobile-nav-links a::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);

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

        .mobile-nav-links a:active {
            transform: translateX(4px);
        }

        .mobile-nav-links a i {
            width: 20px;
            text-align: center;
            font-size: 16px;
            opacity: 0.9;
        }

        .mobile-user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border-radius: 10px;
            margin: 20px 25px;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .mobile-user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 20px;
            color: white;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .mobile-user-info div {
            flex: 1;
        }

        .mobile-user-info div div:first-child {
            font-weight: 700;
            font-size: 16px;
            color: white;
            margin-bottom: 4px;
        }

        .mobile-user-info div div:last-child {
            font-size: 13px;
            color: #bdc3c7;
            background: rgba(255, 255, 255, 0.1);
            padding: 4px 10px;
            border-radius: 20px;
            display: inline-block;
            font-weight: 500;
        }

        /* Mobile menu scrollbar */
        .mobile-menu-content::-webkit-scrollbar {
            width: 6px;
        }

        .mobile-menu-content::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
        }

        .mobile-menu-content::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }

        .mobile-menu-content::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }

        .mobile-logout-link {
            background: linear-gradient(135deg, #0080ffff 0%, #006effff 100%) !important;
            margin-top: 10px;
            border: none;
            color: white;
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 15px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .mobile-logout-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        }

        .mobile-logout-link:hover::before {
            left: 100%;
        }

        .mobile-logout-link:hover {
            background: linear-gradient(135deg, #006effff 0%, #0055ccff 100%) !important;
            transform: translateX(8px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        /* Main Content Styles */
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .intro-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 25px;
            margin-bottom: 30px;
        }

        .intro-title {
            font-size: 24px;
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ecf0f1;
        }

        .intro-content {
            padding: 10px 0;
        }

        .intro-content p {
            color: #34495e;
            line-height: 1.8;
            font-size: 16px;
            margin-bottom: 0;
        }

        .dashboard-header {
            margin-bottom: 30px;
            text-align: center;
        }
        
        .dashboard-header h1 {
            color: #2c3e50;
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .dashboard-header p {
            color: #7f8c8d;
            font-size: 18px;
        }
        
        /* Tree Structure Styles */
        .tree-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .tree-title {
            font-size: 24px;
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ecf0f1;
        }
        
        .tree {
            list-style-type: none;
            padding-left: 0;
        }
        
        .tree li {
            margin: 12px 0;
            position: relative;
            padding-left: 30px;
        }
        
        .tree li .toggle {
            position: absolute;
            left: 0;
            width: 24px;
            height: 24px;
            text-align: center;
            line-height: 24px;
            background: #3498db;
            color: white;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            user-select: none;
        }
        
        .tree li .content {
            font-weight: 600;
            color: #2c3e50;
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 4px;
            transition: all 0.3s;
        }
        
        .tree li .content:hover {
            background-color: #f8f9fa;
        }
        
        .tree ul {
            list-style-type: none;
            padding-left: 40px;
            display: none;
            margin-top: 8px;
        }
        
        .tree li.active > ul {
            display: block;
        }
        
        .tree li.active > .toggle {
            background: #2980b9;
        }
        
        .tree ul li {
            font-size: 15px;
        }
        
        .tree ul li .content {
            font-weight: 500;
            color: #34495e;
        }
        
        .tree ul ul li {
            font-size: 14px;
        }
        
        .tree ul ul li .content {
            font-weight: 400;
            color: #7f8c8d;
        }
        
        /* Footer Styles */
        footer {
            background-color: #2c3e50;
            color: white;
            text-align: center;
            padding: 20px;
            margin-top: 40px;
        }

        /* Topic Content Modal */
        .topic-modal {
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

        .topic-modal-content {
            background-color: white;
            margin: 3% auto;
            padding: 0;
            border-radius: 10px;
            width: 90%;
            max-width: 900px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            position: relative;
            max-height: 90vh;
            overflow-y: auto;
        }

        .topic-modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px 30px;
            border-radius: 10px 10px 0 0;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .topic-modal-header h2 {
            margin: 0;
            font-size: 28px;
        }

        .topic-modal-close {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 32px;
            font-weight: bold;
            cursor: pointer;
            color: white;
            background: rgba(255, 255, 255, 0.2);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .topic-modal-close:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(90deg);
        }

        .topic-modal-body {
            padding: 30px;
        }

        .topic-section {
            margin-bottom: 30px;
        }

        .topic-section h3 {
            color: #2c3e50;
            font-size: 22px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ecf0f1;
        }

        .topic-description {
            color: #34495e;
            line-height: 1.8;
            font-size: 16px;
            margin-bottom: 20px;
        }

        .resource-links {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .resource-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #3498db;
            transition: all 0.3s ease;
        }

        .resource-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-left-color: #2980b9;
        }

        .resource-card h4 {
            color: #2c3e50;
            margin-bottom: 8px;
            font-size: 18px;
        }

        .resource-card p {
            color: #7f8c8d;
            font-size: 14px;
            margin-bottom: 12px;
        }

        .resource-card a {
            color: #3498db;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .resource-card a:hover {
            color: #2980b9;
        }

        .key-topics {
            background: #e8f5e9;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .key-topics h4 {
            color: #27ae60;
            margin-bottom: 15px;
            font-size: 18px;
        }

        .key-topics ul {
            list-style-type: none;
            padding: 0;
        }

        .key-topics li {
            padding: 8px 0;
            color: #2c3e50;
            border-bottom: 1px solid #c8e6c9;
        }

        .key-topics li:last-child {
            border-bottom: none;
        }

        .key-topics li:before {
            content: "✓";
            color: #27ae60;
            font-weight: bold;
            margin-right: 10px;
        }

        .video-section {
            margin-top: 30px;
        }

        .video-container {
            position: relative;
            padding-bottom: 56.25%;
            height: 0;
            overflow: hidden;
            border-radius: 8px;
            margin-top: 15px;
        }

        .video-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
        }
        
        /* Responsive Design */
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
            .tree-container {
                padding: 15px;
            }
            
            .tree ul {
                padding-left: 25px;
            }
            
            .dashboard-header h1 {
                font-size: 28px;
            }
            
            .container {
                padding: 0 15px;
            }

            .topic-modal-content {
                width: 95%;
                margin: 5% auto;
            }

            .topic-modal-header {
                padding: 20px 15px;
            }

            .topic-modal-header h2 {
                font-size: 22px;
                padding-right: 40px;
            }

            .topic-modal-body {
                padding: 20px 15px;
            }

            .resource-links {
                grid-template-columns: 1fr;
            }

            /* Smaller fonts for tree view on mobile */
            .tree li .content {
                font-size: 15px;
                padding: 6px 10px;
            }
            .tree ul li .content {
                font-size: 14px;
            }
            .tree ul ul li .content {
                font-size: 13px;
            }

            /* Smaller fonts for the topic modal on mobile */
            .topic-modal-header h2 {
                font-size: 20px;
            }
            .topic-section h3 {
                font-size: 20px;
            }
            .topic-description, .key-topics li {
                font-size: 15px;
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
            
            .tree-container {
                padding: 12px;
            }
            
            .mobile-menu-content {
                width: 280px;
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
                <a href="dashboard.php" style="background: #34495e;"><i class="fas fa-home"></i> HOME</a>
                <a href="placement_experience.php"><i class="fas fa-book"></i> PLACED EXPERIENCE</a>
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
                <a href="dashboard.php">
                    <i class="fas fa-home"></i>
                    <span>HOME</span>
                </a>
                <a href="placement_experience.php">
                    <i class="fas fa-book"></i>
                    <span>PLACED EXPERIENCE</span>
                </a>
                <a href="chatbot.php">
                    <i class="fas fa-pencil-alt"></i>
                    <span>PREP WITH AI</span>
                </a>
                <?php if (in_array($currentUserType, ['admin', 'faculty'])): ?>
                    <a href="admin_panel.php">
                        <i class="fas fa-user-shield"></i>
                        <span>Admin Panel</span>
                    </a>
                <?php endif; ?>
                
                <!-- Logout as a regular nav link -->
                <a href="logout.php" class="mobile-logout-link">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>LOGOUT</span>
                </a>
            </div>

            <!-- User Info at Bottom -->
            
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <!-- Tree Structure -->
         <div class="intro-container">
            <h2 class="intro-title">Introduction</h2>
            <div class="intro-content"><p>Welcome to the EEE Department's Placement Preparation website! This platform is designed to help you prepare for your placement journey. Whether you're looking for core technical knowledge, software skills, or aptitude training, you'll find a wealth of resources here. Navigate through the preparation topics, explore study materials, Explore the experiences of placed students and leverage AI-powered tools to enhance your placement preparation. In the 'Placement Experience' section, gain insights from real-world experiences shared by your seniors, offering valuable perspectives on company-specific processes and interview strategies. Then, head over to the 'Prep with AI' section, where you can utilize cutting-edge AI tools designed to simulate interview scenarios, assess your aptitude, and provide personalized guidance. These features are designed to give you a competitive edge as you prepare for your placement journey. Good Luck for your placements!</p>

            </div>
        </div>

        <div class="tree-container">
            <h2 class="tree-title">Preparation Topics</h2>
            <ul class="tree" id="preparationTree">
                <!-- CORE -->
                <li>
                    <span class="toggle">+</span>
                    <span class="content">CORE</span>
                    <ul>
                        <!-- ELECTRICAL -->
                        <li>
                            <span class="toggle">+</span>
                            <span class="content">ELECTRICAL</span>
                            <ul>
                                <li><span class="toggle" style="visibility: hidden;"></span><span class="content">ELECTRICAL MACHINES</span></li>
                                <li><span class="toggle" style="visibility: hidden;"></span><span class="content">POWER ELECTRONICS</span></li>
                                <li><span class="toggle" style="visibility: hidden;"></span><span class="content">POWER SYSTEM</span></li>
                                <li><span class="toggle" style="visibility: hidden;"></span><span class="content">PROTECTION SWITCH GEAR</span></li>
                                <li><span class="toggle" style="visibility: hidden;"></span><span class="content">PLC</span></li>
                            </ul>
                        </li>
                        <li>
                            <span class="toggle">+</span>
                            <span class="content">ELECTRONICS</span>
                            <ul>
                                <li>
                                    <span class="toggle">+</span>
                                    <span class="content">ANALOG</span>
                                    <ul>
                                        <li><span class="toggle" style="visibility: hidden;"></span><span class="content">ELECTRIC CIRCUIT ANALYSIS</span></li>
                                        <li><span class="toggle" style="visibility: hidden;"></span><span class="content">ELECTRON DEVICES AND CIRCUITS</span></li>
                                        <li><span class="toggle" style="visibility: hidden;"></span><span class="content">LINEAR INTEGRATED CIRCUITS</span></li>
                                        <li><span class="toggle" style="visibility: hidden;"></span><span class="content">CONTROL SYSTEM</span></li>
                                    </ul>
                                </li>
                                <li>
                                    <span class="toggle">+</span>
                                    <span class="content">DIGITAL</span>
                                    <ul>
                                        <li><span class="toggle" style="visibility: hidden;"></span><span class="content">C</span></li>
                                        <li><span class="toggle" style="visibility: hidden;"></span><span class="content">DIGITAL ELECTRONICS</span></li>
                                        <li><span class="toggle" style="visibility: hidden;"></span><span class="content">DIGITAL SIGNAL PROCESSING</span></li>
                                        <li><span class="toggle" style="visibility: hidden;"></span><span class="content">VERILOG</span></li>
                                        <li><span class="toggle" style="visibility: hidden;"></span><span class="content">VLSI</span></li>
                                        <li><span class="toggle" style="visibility: hidden;"></span><span class="content">STATIC TIMING ANALYSIS</span></li>
                                        <li><span class="toggle" style="visibility: hidden;"></span><span class="content">COMPUTER ARCHITECTURE AND ORGANIZATION</span></li>
                                        <li><span class="toggle" style="visibility: hidden;"></span><span class="content">ELECTRIC CIRCUIT ANALYSIS</span></li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <span class="toggle">+</span>
                            <span class="content">EMBEDDED</span>
                            <ul>
                                <li><span class="toggle" style="visibility: hidden;"></span><span class="content">C</span></li>
                                <li><span class="toggle" style="visibility: hidden;"></span><span class="content">EMBEDDED SYSTEMS</span></li>
                                <li><span class="toggle" style="visibility: hidden;"></span><span class="content">DIGITAL ELECTRONICS</span></li>
                                <li><span class="toggle" style="visibility: hidden;"></span><span class="content">COMPUTER ARCHITECTURE AND ORGANIZATION</span></li>
                                <li><span class="toggle" style="visibility: hidden;"></span><span class="content">MICROPROCESSOR AND MICROCONTROLLER</span></li>
                            </ul>
                        </li>
                    </ul>
                </li>
                
                <!-- SOFTWARE -->
                <li>
                    <span class="toggle">+</span>
                    <span class="content">SOFTWARE</span>
                    <ul>
                        <!-- SOFTWARE CORE -->
                        <li>
                            <span class="toggle">+</span>
                            <span class="content">SOFTWARE CORE</span>
                            <ul>
                                <li><span class="toggle" style="visibility: hidden;"></span><span class="content">OOPS</span></li>
                                <li><span class="toggle" style="visibility: hidden;"></span><span class="content">DATABASE MANAGEMENT SYSTEM</span></li>
                                <li><span class="toggle" style="visibility: hidden;"></span><span class="content">OPERATING SYSTEM</span></li>
                                <li><span class="toggle" style="visibility: hidden;"></span><span class="content">COMPUTER NETWORKS</span></li>
                                <li><span class="toggle" style="visibility: hidden;"></span><span class="content">DEVELOPMENT</span></li>
                                <li><span class="toggle" style="visibility: hidden;"></span><span class="content">COMPUTER ARCHITECTURE AND ORGANIZATION</span></li>
                            </ul>
                        </li>
                        
                        <!-- PROGRAMMING -->
                        <li>
                            <span class="toggle">+</span>
                            <span class="content">PROGRAMMING</span>
                            <ul>
                                <li><span class="toggle" style="visibility: hidden;"></span><span class="content">C</span></li>
                                <li><span class="toggle" style="visibility: hidden;"></span><span class="content">C++</span></li>
                                <li><span class="toggle" style="visibility: hidden;"></span><span class="content">JAVA</span></li>
                                <li><span class="toggle" style="visibility: hidden;"></span><span class="content">PYTHON</span></li>
                                <li><span class="toggle" style="visibility: hidden;"></span><span class="content">DATA STRUCTURES AND ALGORITHM</span></li>
                            </ul>
                        </li>
                    </ul>
                </li>
                
                <!-- APTITUDE -->
                <li>
                    <span class="toggle">+</span>
                    <span class="content">APTITUDE</span>
                    <ul>
                        <!-- QUANTITATIVE -->
                        <li>
                            <span class="toggle">+</span>
                            <span class="content">QUANTITATIVE</span>
                            <ul>
                                <li>
                                    <span class="toggle">+</span>
                                    <span class="content">FUNDAMENTALS</span>
                                    <ul>
                                        <li><span class="toggle" style="visibility: hidden;"></span><span class="content">NUMBERS</span></li>
                                        <li><span class="toggle" style="visibility: hidden;"></span><span class="content">SIMPLE EQUATIONS</span></li>
                                        <li><span class="toggle" style="visibility: hidden;"></span><span class="content">RATIO AND PREPARATION</span></li>
                                        <li><span class="toggle" style="visibility: hidden;"></span><span class="content">AVERAGES</span></li>
                                        <li><span class="toggle" style="visibility: hidden;"></span><span class="content">PERCENTAGE</span></li>
                                        <li><span class="toggle" style="visibility: hidden;"></span><span class="content">LCM AND HCF</span></li>
                                    </ul>
                                </li>
                                <li>
                                    <span class="toggle">+</span>
                                    <span class="content">PERCENTAGE(RELATED)</span>
                                    <ul>
                                        <li><span class="toggle" style="visibility: hidden;"></span><span class="content">PROFIT AND LOSS</span></li>
                                        <li><span class="toggle" style="visibility: hidden;"></span><span class="content">DISCOUNTS</span></li>
                                        <li><span class="toggle" style="visibility: hidden;"></span><span class="content">SIMPLE INTEREST</span></li>
                                        <li><span class="toggle" style="visibility: hidden;"></span><span class="content">COMPUND INTEREST</span></li>
                                    </ul>
                                </li>
                                <li>
                                    <span class="toggle">+</span>
                                    <span class="content">SPEED, DISTANCE AND TIME</span>
                                    <ul>
                                        <li><span class="toggle" style="visibility: hidden;"></span><span class="content">SPEED, DISTANCE AND TIME</span></li>
                                        <li><span class="toggle" style="visibility: hidden;"></span><span class="content">PROBLEMS RELATED TO TRAIN</span></li>
                                        <li><span class="toggle" style="visibility: hidden;"></span><span class="content">BOATS AND STREAMS</span></li>
                                    </ul>
                                </li>
                                <li>
                                    <span class="toggle">+</span>
                                    <span class="content">TIME AND WORK</span>
                                    <ul>
                                        <li><span class="toggle" style="visibility: hidden;"></span><span class="content">TIME AND WORK</span></li>
                                        <li><span class="toggle" style="visibility: hidden;"></span><span class="content">PIPES AND CISTERN</span></li>
                                    </ul>
                                </li>
                                <li>
                                    <span class="toggle">+</span>
                                    <span class="content">PROBABILITY</span>
                                    <ul>
                                        <li><span class="toggle" style="visibility: hidden;"></span><span class="content">PERMUTATION AND COMBINATION</span></li>
                                        <li><span class="toggle" style="visibility: hidden;"></span><span class="content">PROBABILITY</span></li>
                                    </ul>
                                </li>
                                <li>
                                    <span class="toggle">+</span>
                                    <span class="content">DATA INTERPREATION</span>
                                    <ul>
                                        <li><span class="toggle" style="visibility: hidden;"></span><span class="content">TABLES</span></li>
                                        <li><span class="toggle" style="visibility: hidden;"></span><span class="content">BAR CHARTS</span></li>
                                        <li><span class="toggle" style="visibility: hidden;"></span><span class="content">GRAPHS</span></li>
                                        <li><span class="toggle" style="visibility: hidden;"></span><span class="content">PIE CHARTS</span></li>
                                    </ul>
                                </li>
                                <li>
                                    <span class="toggle">+</span>
                                    <span class="content">MISCELLANEOUS</span>
                                    <ul>
                                        <li><span class="toggle" style="visibility: hidden;"></span><span class="content">PROBLEMS ON AGE</span></li>
                                        <li><span class="toggle" style="visibility: hidden;"></span><span class="content">MIXTURES AND ALLIGATIONS</span></li>
                                        <li><span class="toggle" style="visibility: hidden;"></span><span class="content">PARNERSHIP</span></li>
                                        <li><span class="toggle" style="visibility: hidden;"></span><span class="content">TRIGNOMENTRY AND LOGARITHM</span></li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        
                        <!-- LOGICAL -->
                        <li>
                            <span class="toggle">+</span>
                            <span class="content">LOGICAL</span>
                            <ul>
                                <li><span class="toggle" style="visibility: hidden;"></span><span class="content">DIRECTIONS</span></li>
                                <li><span class="toggle" style="visibility: hidden;"></span><span class="content">BLOOD RELATIONS</span></li>
                                <li><span class="toggle" style="visibility: hidden;"></span><span class="content">SYLLOGISMS</span></li>
                                <li><span class="toggle" style="visibility: hidden;"></span><span class="content">CODING AND DECODING</span></li>
                                <li><span class="toggle" style="visibility: hidden;"></span><span class="content">NUMBERS AND LETTERS SERIES</span></li>
                                <li><span class="toggle" style="visibility: hidden;"></span><span class="content">DATA SUFFICIENCY</span></li>
                            </ul>
                        </li>

                        <!-- VERBAL -->
                        <li>
                            <span class="toggle">+</span>
                            <span class="content">VERBAL</span>
                            <ul>
                                <li><span class="toggle" style="visibility: hidden;"></span><span class="content">READING COMPREHENSION</span></li>
                                <li><span class="toggle" style="visibility: hidden;"></span><span class="content">SENTENCE CORRECTIONS</span></li>
                                <li><span class="toggle" style="visibility: hidden;"></span><span class="content">ACTIVE AND PASSIVE VOICE</span></li>
                                <li><span class="toggle" style="visibility: hidden;"></span><span class="content">SYNONYMS AND ANTONYMS</span></li>
                                <li><span class="toggle" style="visibility: hidden;"></span><span class="content">PARAJUMBLES</span></li>
                                <li><span class="toggle" style="visibility: hidden;"></span><span class="content">ARTICLES, PREPOSITONS</span></li>
                                <li><span class="toggle" style="visibility: hidden;"></span><span class="content">IDIOMS AND PHRASES</span></li>
                            </ul>
                        </li>
                        
                        <li>
                            <span class="toggle">+</span>
                            <span class="content">MISCELLANEOUS</span>
                            <ul>
                                <li><span class="toggle" style="visibility: hidden;"></span><span class="content">PUZZLES</span></li>
                            </ul>
                        </li>

                    </ul>
                </li>
                
                <!-- INTERVIEW PREPARATION -->
                <li>
                    <span class="toggle">+</span>
                    <span class="content">INTERVIEW PREPARATION</span>
                    <ul>
                        <li><span class="toggle" style="visibility: hidden;"></span><span class="content">RESUME BUILDING</span></li>
                        <li><span class="toggle" style="visibility: hidden;"></span><span class="content">PROJECTS</span></li>
                        <li><span class="toggle" style="visibility: hidden;"></span><span class="content">SELF INTRO</span></li>
                        <li><span class="toggle" style="visibility: hidden;"></span><span class="content">GROUP DISCUSSION PREPARATION</span></li>
                        <li><span class="toggle" style="visibility: hidden;"></span><span class="content">MANAGERIAL AND HR ROUND PREPARATION</span></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>

    <footer>
        <p>Department of EEE - PSG iTech © <?php echo $currentYear; ?>. All rights reserved.</p>
    </footer>

    <!-- Topic Content Modal -->
    <div id="topicModal" class="topic-modal">
        <div class="topic-modal-content">
            <div class="topic-modal-header">
                <h2 id="modalTopicTitle">Topic Name</h2>
                <span class="topic-modal-close" onclick="closeTopicModal()">&times;</span>
            </div>
            <div class="topic-modal-body" id="modalTopicBody">
                <!-- Content will be loaded here dynamically -->
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded - initializing mobile menu');
        
        // Mobile menu functionality
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileMenu = document.getElementById('mobileMenu');
        const mobileMenuClose = document.getElementById('mobileMenuClose');

        console.log('Mobile menu elements found:', {
            menuBtn: !!mobileMenuBtn,
            menu: !!mobileMenu,
            closeBtn: !!mobileMenuClose
        });

        // Open mobile menu
        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', function(e) {
                console.log('Mobile menu button clicked');
                e.stopPropagation();
                mobileMenu.classList.add('active');
                document.body.style.overflow = 'hidden';
            });
        } else {
            console.error('Mobile menu button not found!');
        }

        // Close mobile menu
        if (mobileMenuClose) {
            mobileMenuClose.addEventListener('click', function(e) {
                console.log('Mobile menu close button clicked');
                e.stopPropagation();
                mobileMenu.classList.remove('active');
                document.body.style.overflow = '';
            });
        } else {
            console.error('Mobile menu close button not found!');
        }

        // Close menu when clicking overlay
        if (mobileMenu) {
            mobileMenu.addEventListener('click', function(e) {
                if (e.target === mobileMenu) {
                    console.log('Mobile menu overlay clicked');
                    mobileMenu.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
        } else {
            console.error('Mobile menu not found!');
        }

        // Mobile menu link handling - SIMPLE AND RELIABLE
        const mobileMenuLinks = document.querySelectorAll('.mobile-nav-links a');
        console.log('Found mobile menu links:', mobileMenuLinks.length);
        
        mobileMenuLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                console.log('Mobile menu link clicked:', this.href);
                
                // Close the menu first
                mobileMenu.classList.remove('active');
                document.body.style.overflow = '';
                
                // For logout, let it proceed normally
                if (this.href.includes('logout.php')) {
                    console.log('Logout link - proceeding normally');
                    return true;
                }
                
                // For other links, let the browser handle navigation normally
                console.log('Navigation link - proceeding normally');
            });
        });

        // Close menu when pressing Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && mobileMenu.classList.contains('active')) {
                mobileMenu.classList.remove('active');
                document.body.style.overflow = '';
            }
        });

            // Tree functionality
            const treeItems = document.querySelectorAll('#preparationTree li');
            
            treeItems.forEach(item => {
                const toggle = item.querySelector(':scope > .toggle');
                const content = item.querySelector(':scope > .content');
                const childList = item.querySelector(':scope > ul');
                
                // Only add toggle functionality if there's a nested list with content
                if (childList && childList.children.length > 0) {
                    toggle.addEventListener('click', function(e) {
                        e.stopPropagation();
                        item.classList.toggle('active');
                        toggle.textContent = item.classList.contains('active') ? '-' : '+';
                    });
                    
                    content.addEventListener('click', function(e) {
                        e.stopPropagation();
                        item.classList.toggle('active');
                        toggle.textContent = item.classList.contains('active') ? '-' : '+';
                    });
                } else {
                    // Remove toggle indicator for items without children
                    toggle.style.visibility = 'hidden';
                    
                    // Add click event to open modal for final topics
                    content.style.cursor = 'pointer';
                    content.addEventListener('click', function(e) {
                        e.stopPropagation();
                        const topicName = content.textContent.trim();
                        openTopicModal(topicName);
                    });
                }
            });
            
            // Auto-expand specific sections by default
            const firstLevelItems = document.querySelectorAll('#preparationTree > li');
            firstLevelItems.forEach(item => {
                const toggle = item.querySelector(':scope > .toggle');
                const childList = item.querySelector(':scope > ul');
                const content = item.querySelector(':scope > .content');
                
                if (childList && content) {
                    const sectionName = content.textContent.trim();
                    // Only open CORE, SOFTWARE, and APTITUDE sections
                    if (sectionName === 'CORE' || sectionName === 'SOFTWARE' || sectionName === 'APTITUDE') {
                        item.classList.add('active');
                        toggle.textContent = '-';
                        childList.style.display = 'block';
                    }
                }
            });
        });

        // Topic Modal Functions
        function openTopicModal(topicName) {
            const modal = document.getElementById('topicModal');
            const modalTitle = document.getElementById('modalTopicTitle');
            const modalBody = document.getElementById('modalTopicBody');
            
            modalTitle.textContent = topicName;
            
            // Load topic content
            loadTopicContent(topicName, modalBody);
            
            // Show modal
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closeTopicModal() {
            const modal = document.getElementById('topicModal');
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }

        function loadTopicContent(topicName, container) {
            // You can fetch content from a PHP file or define it here
            // For now, we'll use a template structure
            
            const content = getTopicTemplate(topicName);
            container.innerHTML = content;
        }

        function getTopicTemplate(topicName) {
            const sharedAptitudeContent = {
                overview: 'Feel free to learn platform contains set of category wise questions for each topic which will help us to solve any kind of problems from that topic. It is recommended to use Feel Free to Learn and Career Ride for studying concepts and refer other YouTube channels as a reference. If possible, join the “All in one Test series“ for quantitative aptitude and logical reasoning in Feel Free to Learn platform to solve more questions on each category (costs around INR 199/-).',
                resources: [
                    { title: 'RS AGARWAL QUANTITATIVE APTITUDE', link: './asset/RS_AGARWAL_QUANTITATIVE_APTITUDE.pdf' },
                    { title: 'A MODERN APPROACH TO VERBAL AND NON VERBAL REASONING', link: './asset/a-modern-approach-to-verbal-amp-non-verbal-reasoning.pdf' }
                ],
                keyTopics: [
                    'PAT PORTAL ALL LEVELS in Aptitude Course (HIGHLY RECOMMENDED)',
                    'Solve All the levels in PAT portal for Aptitude in Aptitude Course as much as possible.',
                    'For Puzzels use GeeksforGeeks - PUZZELES ARTICLE'
                ],
                videos: [
                    { title: 'Career Ride', link: 'https://www.youtube.com/@CareerRideOfficial' },
                    { title: 'Feel Free to Learn', link: 'https://www.youtube.com/@FeelFreetoLearn' }
                ],
                additionalResources: [
                    {title: 'Feel free to learn', link: 'https://feelfreetolearn.videocrypt.in/web/Course/course_details/12577', description: 'Paid Test Series' },
                    { title: 'Indiabix', link: 'https://www.indiabix.com/', description: 'Free Practice Question' },
                    { title: 'Career Ride', link: 'https://example.com/gate', description: 'Free Practice Question' },
                    { title: 'Placement Preparation', link: 'https://www.placementpreparation.io/', description: 'Company Specific Questions' }
                ]
            };

            const aptitudeTopics = ['PUZZLES', 'NUMBERS', 'SIMPLE EQUATIONS', 'RATIO AND PREPARATION', 'AVERAGES', 'PERCENTAGE', 'LCM AND HCF', 'PROFIT AND LOSS', 'DISCOUNTS', 'SIMPLE INTEREST', 'COMPUND INTEREST', 'SPEED, DISTANCE AND TIME', 'PROBLEMS RELATED TO TRAIN', 'BOATS AND STREAMS', 'TIME AND WORK', 'PIPES AND CISTERN', 'PERMUTATION AND COMBINATION', 'PROBABILITY', 'TABLES', 'BAR CHARTS', 'GRAPHS', 'PIE CHARTS', 'PROBLEMS ON AGE', 'MIXTURES AND ALLIGATIONS', 'PARNERSHIP', 'TRIGNOMENTRY AND LOGARITHM', 'DIRECTIONS', 'BLOOD RELATIONS', 'SYLLOGISMS', 'CODING AND DECODING', 'NUMBERS AND LETTERS SERIES', 'DATA SUFFICIENCY', 'READING COMPREHENSION','SENTENCE CORRECTIONS','ACTIVE AND PASSIVE VOICE', 'SYNONYMS AND ANTONYMS', 'PARAJUMBLES', 'ARTICLES, PREPOSITONS', 'IDIOMS AND PHRASES'];

            const topicContent = {
                'PROTECTION SWITCH GEAR':{
                    overview: 'Protec on and Switchgear is a core subject focuses on the principles, design, and opera on of protec ve relays, circuit breakers, fuses, isolators, and switchgear assemblies used in power genera on, transmission, and distribu on systems. This subject is highly valuable for students aiming for careers in power u li es, electrical design industries, manufacturing units, and renewable energy sectors. The knowledge gained is applicable in companies such as Sobha construc on, Schneider Electric India, Siemens Energy Limited, Voltas Limited, Hitachi, ELGi Equipment Ltd, Saipem India Projects, Anora Instrumenta on.',
                    resources: [
                        { title: 'Principles of Power System by V.K. Mehta', link: 'https://drive.google.com/drive/folders/1xoLbthyw-jzbBpJOyq_rP0HnFO3nvI0_' },
                        { title: 'Protection and Switchgear - Bakshi ', link: 'https://drive.google.com/drive/folders/1i_FY9Cx2sZFpuIBCHfL3IRrrHKGV_tlV'}
                    ],
                    keyTopics: [
                        'Types of faults – symmetrical and unsymmetrical faults, their effects on power systems',
                        'Operating principle and types of circuit breakers (air, oil, SF6, vacuum)',
                        'Working principle and types of protective relays (electromagnetic, static, numerical)',
                        'Arc formation and methods of arc extinction and Fuse characteristic and selection criteria'
                    ],
                    videos: [
                        { title: 'Protec on and Switchgear Engineering- Ekeeda', link: 'https://www.youtube.com/playlist?list=PLm_MSClsnwm_MGFxcos9bQ1BPujUF7YEx'},
                        { title: 'Circuit Breakers explained- Electrical lectures', link: 'https://www.youtube.com/@electricallectures/playlists'}

                    ],
                    additionalResources: [
                        {title: 'Protection and Switchgear Quiz – Electrical4U.', link: '', description: 'Helpful for first round technical MCQ ques ons.'},
                        {title: 'Electrical4U – Power System Protection Tutorials', link: '', description: 'Easy-to-understand the concepts of P&S.'},
                        {title: 'Top Technical Interview Questions – Protection and Switchgear', link: '', description: 'collection of commonly asked interview quesions with clear explanations on relays, circuit breakers, and fault protection'}
                    ]
                },
                'EMBEDDED SYSTEMS':{
                    overview: '',
                    resources: [
                        { title: '', link: '' }
                    ],
                    keyTopics: [
                        ''
                    ],
                    videos: [
                        { title: '', link: '' }
                    ],
                    additionalResources: [
                        {title: '', link: '', description: '' }
                    ]
                },
                'PLC':{
                    overview: 'Programmable Logic Controller is one of the most essenensial subjects in Electrical, Electronics, and  Instrumentation Engineering. It focuses on the automation and control of industrial processes using programmable controllers instead of conventional relay-based systems. Students learn how to design, program, and troubleshoot automated systems that control motors, conveyors, robotic arms, and production lines. The subject covers ladder logic, input/output modules, sensors, actuators, timers, and counters, which are crucial for modern industrial automation. The knowledge gained is applicable in companies such as Schneider Electric India, Asian Paints, Hitachi, Voltas Limited (A Tata Enterprise), ELGi Equipments Ltd, Siemens Energy Limited, Saipem India Projects Pvt. Ltd, Caterpillar, Anora Instrumenta on Pvt. Ltd. ',
                    resources: [
                        { title: 'Programmable logic controllers - frank petruzella', link: 'https://drive.google.com/drive/folders/1EPxNLyUE5Pqhl3XNvr6rbuRdsdb7yaEe' }
                    ],
                    keyTopics: [
                        ''
                    ],
                    videos: [
                        { title: 'Introduction to PLC | Siemens PLC Training Course', link: 'https://www.youtube.com/watch?v=jzy0so3h_kc' }
                    ],
                    additionalResources: [
                        {title: 'Important PLC Interview Questions and Answers ', link: 'https://dipslab.com/plc-interview-questions-answers/', description: '' },
                        {title: 'Top 100 PLC Interview Questions and Answers', link: 'https://www.ambitionbox.com/skills/plc-interview-questions', description: ''}
                    ]
                },
                'DIGITAL SIGNAL PROCESSING':{
                    overview:'Digital Signal Processing (DSP) involves the analysis, manipulation, and transformation of signals using digital systems. It includes sampling, filtering, convolution, transforms, and applications in audio, communication, biomedical, and image processing' ,
                    resources: [
                        { title: 'Digital signal processing -Ananda kumar', link: 'https://drive.google.com/drive/folders/1d3mogb7O5-YEUQg3qbZ09HOaskHBt3hd'},
                        { title: 'Digital Signal Processing - Nalbalwar', link: 'https://drive.google.com/drive/folders/10JcuUntOtT63SJKE7e8bkKbJEfViUwf9' }
                    ],
                    keyTopics: ['Sampling theorem and aliasing',
                                'Discrete-time signals and systems',
                                'Z-transform and its properties',
                                'DFT and FFT algorithms',
                                'FIR filter design (Window method)',
                                'IIR filter design (Butterworth, Chebyshev)',
                                'Convolution (linear & circular)',
                                'Correlation and spectral analysis',
                                'DSP applications in audio, speech, biomedical, and communication'
                    ],
                    videos: [
                        { title: 'Signals and systems-Neso Academy' , link:'https://youtube.com/playlist?list=PLBlnK6fEyqRhG6s3jYIU48CqsT5cyiDTO&si=1kZOImzTw1ODynME' }
                    ],
                    additionalResources: [
                        {title:'Sanfoundry DSP Questions', link:'https://www.sanfoundry.com/1000-digital-signal-processing-questions-answers/', description:' DSP 1000 Practice Questions' }
                    ]
                },
                'POWER SYSTEM':{
                    overview: 'Power Systems is the backbone of electrical engineering, focusing on the generation, transmission, distribution, and control of electric power. It integrates concepts from electrical machines, power electronics, and control systems to ensure reliable, efficient, and stable power delivery. The subject covers power generation from conventional and renewable sources, economic dispatch, transmission line performance, system protection, load flow studies, fault analysis, stability, and compensation techniques. Understanding how power is generated, transmitted, and maintained under various operating and fault conditions is essential for power sector roles, grid operation, and utility based placements.',
                    resources: [
                        { title: 'Principles of Power System - V K Mehta', link: 'https://drive.google.com/drive/folders/1tFu4WGV8vjxKUCycnHEmlEh92aS5jcOu' }
                    ],
                    keyTopics: [
                        'Power Generation and Transmission: Know the basics of conventional and non-conventional generation, economic load dispatch, and HVDC/AC transmission with their parameters, losses, and efficiency considerations.',
                        'System Analysis and Control: Understand per-unit systems, load flow methods (Gauss-Seidel, Newton-Raphson), voltage/frequency control, and economic load dispatch - crucial for grid performance analysis.',
                        'Protection and Fault Studies: Be clear with symmetrical components, sequence networks, fault types, and protection schemes (overcurrent, differential, distance) along with circuit breaker operation and ratings.',
                        'Stability and Compensation: Grasp system stability concepts (steady-state, transient, dynamic), equal area criterion, and compensation methods (series/shunt, FACTS devices) for maintaining power quality and system reliability.'
                    ],
                    videos: [
                        { title: 'Power System - Neso Academy', link: 'https://www.youtube.com/playlist?list=PLBlnK6fEyqRi17rO6B3_XHtMqAKXQ0Tp4' },
                        { title: 'Power System - Lectures in Electrical Engineering', link: 'https://www.youtube.com/playlist?list=PL_mruqjnuVd9eT-Lmjdbr0UQVcooVy-tU'}
                    ],
                    additionalResources: [
                        {title: 'ETAB Software', link: 'https://drive.google.com/drive/folders/1JUGw4M1hQU5b9BxzZokTT3y6eQ2oI8wT', description: '' },
                        {title: 'Electrical Power System', link: 'https://www.electrical4u.com/power-system/', description: 'Electrical4U' },
                        {title: 'Power System Interview Questions', link: 'https://forumelectrical.com/power-system-interview-questions/', description: 'ForumElectrical.Com'},  
                        {title: 'Interview Preparation', link: 'https://www.withoutbook.com/InterviewQuestionList.php?tech=122&dl=Top&s=Power%20System%20Interview%20Questions%20and%20Answers', description: 'Without Book'}  
                    ]
                },
                'POWER ELECTRONICS':{
                    overview: 'Power Electronics deals with the conversion, control, and conditioning of electric power using semiconductor devices. It bridges electrical machines and electronic control, enabling efficient energy conversion in modern systems such as motor drives, converters, and renewable energy applications. The subject focuses on power semiconductor devices like SCR, MOSFET, and IGBT, their switching characteristics, and gate control methods. It also covers converter types - DC-DC (buck, boost, buck-boost), AC-DC (rectifiers), and AC-AC (inverters and choppers) - along with modulation techniques, harmonic analysis, and applications in speed control, voltage regulation, and industrial automation.',
                    resources: [
                        { title: 'Power Electronics - Bhimbhra', link: 'https://drive.google.com/drive/folders/1UG_bvIjiMBtYjvzhtYV4QZB8IqVupjFj' }
                    ],
                    keyTopics: [
                        'Device Knowledge: Understand the characteristics, triggering, and switching behavior of SCR, MOSFET, and IGBT - the core components in converters and drives.',
                        'Converter Fundamentals: Know the operation, waveforms, and efficiency of DC-DC converters, rectifiers (AC-DC), and inverters (AC AC), including CCM and DCM modes.',
                        'Control Techniques: Learn about firing angle control, PWM methods (especially SPWM), and how they influence output voltage, harmonics, and power factor.',
                        'Applications and Analysis: Focus on how converters are used in motor speed control, power supplies, and renewable energy systems, emphasizing performance parameters like THD and efficiency - common in placement interviews. '
                    ],
                    videos: [
                        { title: 'Power Electronics - Lectures in Electrical Engineering', link: 'https://www.youtube.com/playlist?list=PL_mruqjnuVd9_mwhgK3nAy-cHyslXCnRk' },
                        { title: 'Power Electronics in Tamil', link: 'https://www.youtube.com/playlist?list=PLMC_fsTBvdNivP8fPZrVsW7rPW1EMlqOM'}
                    ],
                    additionalResources: [
                        { title: 'Power Electronics - Circuit Diagram', link: 'https://www.electrical4u.com/electrical-engineering-articles/power-electronics/', description: 'Electrical4U' },
                        { title: 'MCQs on Power Electronics', link: 'https://www.electrical4u.com/electrical-mcq.php?subject=power-electronics&page=1', description: 'Electrical4U'},
                        { title: 'Top Interview Questions', link: 'https://www.learnelectronicsindia.com/post/top-30-interview-questions-answers-for-power-electronics-engineer?srsltid=AfmBOooW0q-DKUyHf5kxCyA5-21xrkFMCJJHvWdqqEcXaSF1PMpArNzM', description: 'Learn Electronics India'}

                    ]
                },
                'ELECTRICAL MACHINES':{
                    overview: 'Electrical Machines form the foundation of power conversion systems, covering the principles, construction, performance, and control of transformers, DC machines, induction motors, and synchronous machines. The subject explains how electrical energy is converted to mechanical energy (and vice versa) through electromagnetic induction. It includes detailed study of transformers for power transfer, DC machines for variable speed and torque control, induction motors for industrial drives, and synchronous machines for power generation and factor correction. Understanding machine characteristics, equivalent circuits, testing methods, losses, and efficiency is crucial for design, operation, and maintenance in power and industrial systems.',
                    resources: [
                        { title: 'ELECTRICAL MACHINES-I - Bhakshi', link: 'https://drive.google.com/drive/folders/13HH3MQTIS1jx3ocl3bk__Z_mxSWpi_CM' },
                        { title: 'ELECTRICAL MACHINES-II - Bhakshi', link: 'https://drive.google.com/drive/folders/1Ab2AvVNSji6_ezLmpi7dqrQsupAzwYrE'},
                        { title: 'Electrical Interview Question - Answer', link: 'https://drive.google.com/drive/folders/1zgL5CFeOtXBz_sncbGsAIU2ENjSg3W92'}
                    ],
                    keyTopics: [
                        'Core Understanding: Know the working principles, EMF equations, and construction details of transformers, DC, induction, and synchronous machines.',
                        'Performance Analysis: Be able to draw and interpret equivalent circuits, phasor diagrams, torque-speed characteristics, and efficiency conditions.',
                        'Testing and Control: Understand standard tests (OC, SC, load tests) and control methods for speed, torque, and voltage regulation.',
                        'Applications and Problem Solving: Focus on real-world applications in power systems, motor drives, and generation—key areas for technical interviews and aptitude tests.'
                    ],
                    videos: [
                        { title: 'ELECTRICAL MACHINES - Dr.Jayaudhaya', link: 'https://www.youtube.com/playlist?list=PLQTXUrE24B81jr2iEuIF9CpsB3JPs_SOc' },
                        { title: 'ELECTRICAL MACHINES - NPTEL', link: 'https://www.youtube.com/playlist?list=PLp6ek2hDcoNCANsWM2mw3qi0387BhfLyV' }
                    ],
                    additionalResources: [
                        {title: 'Types of Electric Machines', link: 'https://www.geeksforgeeks.org/electrical-engineering/types-of-electric-machines/', description: 'GeeksforGeeks Article' },
                        { title: 'Electric Machines Transformers Generators and Motors', link: 'https://www.electrical4u.com/electric-machines/', description: 'Electrical4U' },
                        { title: 'Top Electrical Machines Interview Questions', link: 'https://www.iscalepro.com/post/electrical-machines-interview-questions/', description: 'iScalePro' },
                        { title: 'MCQs on Electrical Machines', link: 'https://www.electrical4u.com/electrical-mcq.php?subject=electrical-machines&page=1', description: 'MCQs - Electrical 4 U' }
                    ]
                },
                'ELECTRIC CIRCUIT ANALYSIS':{
                    overview: 'Electric Circuit Analysis is a fundamental subject in electrical engineering that deals with the study of electrical circuits and their behavior. It covers various concepts such as Ohm\'s Law, Kirchhoff\'s Laws, network theorems, transient and steady-state analysis, and AC/DC circuit analysis. Understanding these concepts is crucial for designing and analyzing electrical systems.',
                    resources: [
                        { title: 'CIRCUIT THEORY by Nagoor kani', link: 'https://drive.google.com/drive/folders/1seIKBMPNcri2GK5WYv9Nwt4z81cCOqvb' },
                        { title: 'Fundamentals of Electric Circuits by Charles Alexander Matthew Sadiku', link: 'https://drive.google.com/drive/folders/1e40IHOlnd74x2aA96AGYtZ4MZaYpP7Aj'}
                    ],
                    keyTopics: [
                        'Focus on understanding circuit theorems like Thevenin\'s and Norton\'s theorems.',
                        'Practice solving both DC and AC circuit problems to strengthen your analytical skills.'
                    ],
                    videos: [
                        { title: 'Electric Circuits - Neso Academy', link: 'https://www.youtube.com/watch?v=NEhH6C7Fzw4&list=PLBlnK6fEyqRgLR-hMp7wem-bdVN1iEhsh' },
                        { title: 'Network Analysis (Full Course) - Unacademy', link: 'https://www.youtube.com/watch?v=X2y1LI9Tq3w&list=PLs5_Rtf2P2r7hkaum0d0LwgWq7K6Ducxf' },
                        { title: 'Network Analysis / Network Theory - ALL ABOUT ELECTRONICS', link: 'https://www.youtube.com/watch?v=zUGaU4kZpag&list=PLwjK_iyK4LLBN9RIDQfl9YB4caBYyD_uo'}
                    ],
                    additionalResources: [
                        {title: 'Electric Circuits - Practice Paper', link: 'https://practicepaper.in/gate-ee/electric-circuits', description: 'Interview Questions - MCQs'}
                    ]
                },
                'CONTROL SYSTEM': {
                    overview: 'Control Systems is a crucial subject in electrical engineering that focuses on the behavior of dynamic systems and how to control them effectively. It covers topics such as feedback systems, stability analysis, time and frequency domain analysis, and controller design. Understanding control systems is essential for various applications in automation, robotics, and industrial processes.',
                    resources: [
                        { title: 'Control Systems - Bhakshi', link: 'https://drive.google.com/drive/folders/1f8pgfswEkcihaS8zu1SrsbKiS9W2W3aT'},
                        { title: 'HandWritten Notes', link: 'https://drive.google.com/drive/folders/1I1Heudugwiw2INTQFbnJu84t84otyIZr' }
                    ],
                    keyTopics: [
                        'Practice control system problems, as they are commonly asked in the first round of core company interviews.',
                        'Working on a control systems project can significantly increase your chances of getting placed in core or automation-based roles.'
                    ],
                    videos: [
                        { title: 'Control Systems - Neso Academy', link: 'https://www.youtube.com/watch?v=gp6-G68_uag&list=PLtLXpzQrbAwwDYYQTgII6PfZGit06mwDq' }
                    ],
                    additionalResources: []
                },
                'LINEAR INTEGRATED CIRCUITS': {
                    overview: 'Linear Integrated Circuits (ICs) are essential components in analog electronics, used in various applications such as amplification, filtering, and signal processing. This subject covers the principles of operation, characteristics, and applications of different types of linear ICs, including operational amplifiers, comparators, and voltage regulators.',
                    resources: [
                        { title: 'Linear Integrated Circuits - Roy Choudhury', link: 'https://drive.google.com/drive/folders/1rxrcNfTw8HIjqlH4dIctrud5Lejl2VNM'},
                        { title: 'Linear Integrated Circuits - Bhakshi', link: 'https://drive.google.com/drive/folders/1qFs3W2p3OsVxEBu_eRWOL7922EpLPGba' }
                    ],
                    keyTopics: [
                        'Focus on understanding the working principles of operational amplifiers and their applications.',
                        'Working on a Analog related project can significantly increase your chances of getting placed in core roles.'
                    ],
                    videos: [
                        { title: 'Analog Electronics - Engineering Funda', link: 'https://www.youtube.com/watch?v=b8-Q9ypooHA&list=PLgwJf8NK-2e5u1DJ5jfTcj6m1GX-cEdm8' },
                    ],
                    additionalResources: [
                        { title: 'Operational Amplifiers - Practice Paper', link: 'https://practicepaper.in/gate-ee/operational-amplifiers', description: 'Practice GATE question will make easy for craking first round' },
                    ]
                },
                'ELECTRONIC DEVICES AND CIRCUITS': {
                    overview: 'Electron Devices and Circuits is a fundamental subject in electrical engineering that focuses on the study of semiconductor devices and their applications in electronic circuits. It covers topics such as diodes, transistors, and their configurations, as well as various electronic circuits used in amplifiers, oscillators, and switching applications.',
                    resources: [
                        { title: 'Electronic Devices and Circuits - Robert L. Boylestad Louis Nashelsky', link: 'https://drive.google.com/drive/folders/1VazJJeWaj-mO3tK_yZKG5AxN4RfXQObt'}                   
                    ],
                    keyTopics: [
                        'Unacademy Lectures and ALL ABOUT ELECTRONICS are recommended to use a primary source for learning analog electronics and use other resources as your supplement to aid your understanding.',
                        'Focus on understanding the characteristics and applications of diodes and transistors.',
                        'Practice analyzing and designing basic electronic circuits to strengthen your problem-solving skills.'
                    ],
                    videos: [
                        { title: 'Analog Electronics - Unacademy', link: 'https://www.youtube.com/watch?v=XG3cVoUh7wc&list=PLs5_Rtf2P2r674CTMNJ3odeHk9Wtb-WWl'},
                        { title: 'Electronic Devices and Circuits - Neso Academy', link: 'https://www.youtube.com/playlist?list=PLBlnK6fEyqRiw-GZRqfnlVIBz9dxrqHJS' },
                        { title: 'Electronic Devices and Circuits - All About Electronics', link: 'https://www.youtube.com/playlist?list=PLwjK_iyK4LLBVM18VZ7JKW-q88FAtnr8_' },
                    ],
                    additionalResources: [
                        { title: 'Electronic Devices - Practice Paper', link: 'https://practicepaper.in/gate-ec/electronic-devices', description: 'Practice GATE question will make easy for craking first round' },
                    ]
                },
                'DIGITAL ELECTRONICS': {
                    overview: 'Digital Electronics is a fundamental subject that forms the basis for understanding modern electronic systems. It covers topics such as logic gates, flip-flops, multiplexers, and digital circuits, which are essential for various applications in electronics and computer engineering.',
                    resources: [
                        { title: 'Digital Logic and Computer Design by MORRIS MANO', link: 'https://drive.google.com/drive/folders/193-3AoPferXCECeU7sZ4K3gfxGaWKu2Q' },
                        { title: 'Digital Electronics - Bhakshi', link: 'https://drive.google.com/drive/folders/1XvkhiIDzNwj4YoGdoP2GsnP3Hb410kDj'}
                    ],
                    keyTopics: [
                        'Focus on understanding the working of different logic gates and their applications.',
                        'Practice designing and analyzing digital circuits to strengthen your problem-solving skills.'
                    ],
                    videos: [
                        { title: 'Digital Electronics - Neso Academy', link: 'https://www.youtube.com/playlist?list=PLBlnK6fEyqRjMH3mWf6kwqiTbT798eAOm' },
                        { title: 'Digital Electronics - Tutorials Point', link: 'https://www.youtube.com/playlist?list=PLWPirh4EWFpHk70zwYoHu87uVsCC8E2S-' },
                        { title: 'Digital Electronics - Engineering Funda', link: 'https://www.youtube.com/playlist?list=PLgwJf8NK-2e7nYSG31YWEUfwgAp2uIOBY'}
                    ],
                    additionalResources: [
                        { title: 'Types Questions', link: 'https://ribbon-tapir-f60.notion.site/DIGITAL-ELECTRONICS-69176255292747b7b6f423096f45fdff', description: 'This notes will give a deep view of kind of question asked in interview'},
                        { title: 'GFG Article', link: 'https://www.geeksforgeeks.org/digital-logic/digital-electronics-logic-design-tutorials/', description: 'Comprehensive resource for learning digital electronics concepts.' },
                        { title: 'Digital Circuits - Practice Pare MCQ', link: 'https://practicepaper.in/gate-ec/digital-circuits', description: 'Practice GATE question will make easy for craking first round' },
                        { title: 'GATE MCQs', link: 'https://questions.examside.com/past-years/gate/question/pconsider-a-boolean-gate-d-where-the-output-y-is-related-gate-ece-network-theory-network-elements-chuu6fngykrxu69h', description: '' }
                    ]
                },
                'VERILOG': {
                    overview: 'Verilog is a hardware description language (HDL) used to model electronic systems. It is widely used in the design and verification of digital circuits, including FPGAs and ASICs. Learning Verilog is essential for understanding digital design and hardware implementation.',
                    resources: [],
                    keyTopics: [
                        'Understand the syntax and semantics of Verilog for designing digital circuits.',
                        'Practice writing Verilog code for various digital components and systems.'
                    ],
                    videos: [
                        { title: 'Verilog for an FPGA Engineer Paid course', link: 'https://www.udemy.com/course/verilog-for-an-engineer-with-xilinx-vivado-design-suite/?couponCode=KEEPLEARNING' },
                        { title: 'Verilog by NPTEL', link: 'https://onlinecourses.nptel.ac.in/noc24_cs61/preview' }
                    ],
                    additionalResources:[
                        { title: 'Verilog Practice - HDLbits', link: 'https://hdlbits.01xz.net/wiki/Problem_sets#Getting_Started', description: '' },
                        { title: 'Verilog Projects', link: 'https://www.fpga4student.com/p/verilog-project.html', description: 'hese Verilog projects are very basic and suited for students to practice'},
                        { title: 'Verilog MCQs', link: 'https://technobyte.org/verilog-quiz-mcqs-interview-questions/', description: 'Interview Questions'}
                    ]
                },
                'VLSI': {
                    overview: 'VLSI (Very Large Scale Integration) is a technology that allows the integration of thousands to millions of transistors on a single chip. It is a crucial subject in electrical engineering that focuses on the design and fabrication of integrated circuits (ICs) used in various electronic devices.',
                    resources: [
                        { title: 'VLSI Design Complete Road map', link: 'https://drive.google.com/drive/folders/1y9UyraKfW_B-1-Cw4X1qL37510niU3ar'},
                        { title: 'Handwritten notes', link: 'https://drive.google.com/drive/folders/1iWWhN3HIFqR6ohnY5rg1SQ3Rokxmz480'}
                    ],
                    keyTopics: [
                        'DIGITAL IC DESIGN BY DR.JANAKIRAMAN(NPTEL)',
                        'VLSI PHYSICAL DESIGN BY INDRANIL SENGUPTA(NPTEL)',
                        'CMOS VLSI DESIGN BY SUDEB DASGUPTA(NPTEL)',
                        'Check out the above three courses to get started with your career in VLSI design.'
                    ],
                    videos: [
                        { title: 'VLSI Design - Electronics Insight', link: 'https://www.youtube.com/watch?v=0gZWkSRdj4k&list=PLS3FbwW7PEokm_HaZ711P7IlEMd2ojXsf'},
                        { title: 'VLSI - Engineering Funda', link: 'https://www.youtube.com/watch?v=ONU-zFBzlXo&list=PLgwJf8NK-2e6au9bX9P_bA3ywxqigCsaC'}
                    ],
                    additionalResources:[]
                },
                'STATIC TIMING ANALYSIS': {
                    overview: 'Static Timing Analysis (STA) is a crucial step in the VLSI design process used to verify the timing performance of a digital circuit without applying input vectors. It ensures that the circuit meets all timing requirements such as setup time, hold time, and clock constraints for reliable operation.',
                    resources:[],
                    keyTopics: [
                        'This topic is widely asked in the high paying companies such as Texas Instruments , Qualcomm etc…  for the role of digital design engineer.Have a Good Understanding on this particular subject.'
                    ],
                    videos: [
                        { title: 'Static Timing Analysis - Yash Jain', link: 'https://www.youtube.com/watch?v=xCA54Qu4WtQ&list=PLpCkjM331Aa8JNoZ1s1o1txve2wlf9pCP'},
                        { title: 'Static Timing Analysis Interview Questions - Technical Bytes', link: 'https://www.youtube.com/watch?v=8Fi6TNz-Gc8&list=PLPmSCnkkX4qu3y6qEJ8xptEQKKG2_NnDj'},
                        { title: 'Advanced VLSI Design - Sanjay Vidhyadharan', link: 'https://www.youtube.com/watch?v=O1Af6bIkXNA&list=PLfMCiCIRnpUkZjEsg1bc4DNZFa4P0DzBx&index=28'}
                    ],
                    additionalResources:[
                        { title: 'VSD - Static Timing Analysis - I', link: 'https://www.udemy.com/course/vlsi-academy-sta-checks/?couponCode=KEEPLEARNING', description: 'Udamey Paid courses'},
                        { title: 'VSD - Static Timing Analysis - II', link: 'https://www.udemy.com/course/vlsi-academy-sta-checks-2/?couponCode=LETSLEARNNOW', description: 'Udamey Paid courses'}
                    ]
                },
                'MICROPROCESSOR AND MICROCONTROLLER': {
                    overview: 'Microprocessors and Microcontrollers are essential components in embedded systems and digital electronics. A microprocessor is a general-purpose system that executes instructions, while a microcontroller is a compact integrated circuit designed to govern specific operations in embedded applications. Understanding both is crucial for designing and implementing electronic systems.',
                    resources: [
                        { title: 'Microprocessor (8085) And its Applications - Nagoor kani', link: 'https://drive.google.com/drive/folders/1CWLBdh6_ccp5LgNU156qt8TaeCDIUbht' },
                        { title: 'The 8051 Microcontroller and Embedded Systems - Muhammad Ali Mazidi', link: 'https://drive.google.com/drive/folders/1TVwKv0inqmYF9QFvLKsJD2oAy_CtfdTa'}
                    ],
                    keyTopics: [
                        'Focus on learning MPMC in class itself is sufficient to crack the interview'
                    ],
                    videos: [],
                    additionalResources: [
                        { title: 'Microprocessor Interview Questions - Educba', link: 'https://www.educba.com/microprocessor-interview-questions/', description: 'More This page is an article' },
                        { title: 'Microprocessor Interview Questions', link: 'https://www.onlineinterviewquestions.com/microprocessor-interview-questions/', description: 'Practice GATE question will make easy for craking first round' }
                    ]
                },
                'C': {
                    overview: 'C is a basic and important programming language that almost all companies, both core and IT, expect us to know. Our college offers a C programming course handled by Karthik and his team, and our seniors have mentioned that it is very useful.',
                    resources: [
                        { title: 'C Programming Notes', link: 'https://drive.google.com/drive/folders/1fDzM5-5I7KSEmcLge8g8kRVBWZHEltV-' },
                        { title: 'C BYTS class screenshots', link: 'https://drive.google.com/drive/folders/1xFqM34_Nmh1zDuVTCQnR6CwPyAEi0LFI' },
                        { title: 'C Last minute notes ', link: 'https://ribbon-tapir-f60.notion.site/C-programming-BYTS-aed99916110546728e3647675167b273?pvs=4' }
                    ],
                    keyTopics: [
                        'Use the above Last minute notes for quick review, not for detailed learning. They are great for recalling C concepts quickly'
                    ],
                    videos: [
                        { title: 'C Programming Tutorial - BYTS', link: 'https://byts.co.in/dashboard' }
                    ],
                    additionalResources: [
                        { title: 'HackerRank', link: 'https://www.hackerrank.com/domains/c', description: 'C Programming Practice' },
                        { title: 'Leetcode', link: 'https://leetcode.com/problemset/', description: 'DSA sheet by Leetcode' }
                    ]
                },
                'C++': {
                    overview: 'C++ serves as a bridge between procedural and object-oriented programming. By learning C++, you can understand OOP concepts effectively, which are essential for mastering Data Structures and Algorithms (DSA) - a key area for placement preparation.',
                    resources: [
                        { title: 'C++ Programming Notes', link: 'https://drive.google.com/drive/folders/1MZg1LY_o6H76EbgB6KRLQyNBrg6ciLjg' },
                        { title: 'C++ BYTS class screenshots', link: 'https://drive.google.com/drive/folders/1zRdk11y-_cu6IrNxlbZX9nCl1l_HH_RU' }
                    ],
                    keyTopics: [
                        'Learn both the code and concepts of OOP thoroughly to build a strong programming foundation.',
                        'Refer to the “Take U Forward” YouTube channel and Website, which is widely used and highly recommended by our seniors for its useful explanations and placement-oriented content.'
                    ],
                    videos: [
                        { title: 'Take U Forward - C++', link: 'https://www.youtube.com/watch?v=EAR7De6Goz4' },
                        { title: 'C++ JENNYS LECTURES FULL COURSE ', link: 'https://www.youtube.com/watch?v=oOmbSpOzvYg&list=PLdo5W4Nhv31YU5Wx1dopka58teWP9aCee' }
                    ],
                    additionalResources: [
                        { title: 'C++ Programming Tutorial - BYTS', link: 'https://byts.co.in/dashboard', description: 'This course can be accessed only through our college email ID.' },
                        { title: 'Take U Forward - DSA sheet', link: 'https://takeuforward.org/strivers-a2z-dsa-course/strivers-a2z-dsa-course-sheet-2/', description: 'Try complete All the Problems' }
                    ]
                },
                'JAVA': {
                    overview: 'Java is an important language for IT placements, as companies like SAP and Oracle expect good knowledge of it. However, core companies generally do not require Java, focusing more on fundamental or hardware-related skills.',
                    resources: [],
                    keyTopics: [
                        'Having a good grasp of Core Java and OOP concepts will help you clear technical interviews easily.',
                        'Learning frameworks like Spring Boot will be an added advantage, especially for backend and full-stack development roles in IT companies.'
                    ],
                    videos: [
                        { title: 'Java Tutorial For beginners - Telusko', link: 'https://www.youtube.com/playlist?list=PLsyeobzWxl7pe_IiTfNyr55kwJPWbgxB5' },
                        { title: 'Java in Tamil - Code io', link: 'https://www.youtube.com/watch?v=jyUs-TzaBQE&list=PLhP5RsB7fhE21o8D5teJjwJpDRRpXbSRI' }
                    ],
                    additionalResources: []
                },
                'PYTHON': {
                    overview: 'Python is a versatile language used in various fields such as web development, data science, machine learning, and automation. It is known for its simplicity and readability, making it a great choice for beginners and experienced developers alike.',
                    resources: [],
                    keyTopics: [
                        'Focus on understanding Python syntax and libraries commonly used in data science and data analytics.',
                        'Familiarity with Python frameworks like Django or Flask can be an added advantage for backend or full-stack roles.'
                    ],
                    videos: [
                        { title: 'Python Tutorial for Beginners - Corey Schafer', link: 'https://www.youtube.com/playlist?list=PL-osiE80TeTt2d9bfVyTiXJA-UTHn6WwU' },
                        { title: 'Python in Tamil - Code io', link: 'https://www.youtube.com/watch?v=dHzYLjfr-uY&t=5s' }
                    ],
                    additionalResources: [
                        { title: 'Automate the Boring Stuff with Python', link: 'https://automatetheboringstuff.com/', description: 'Great for beginners to learn practical Python applications.' }
                    ]
                },
                'DATA STRUCTURES AND ALGORITHM': {
                    overview : 'Data Structures and Algorithms (DSA) form the backbone of problem-solving in programming and are crucial for placement interviews across both IT and core companies. Strong DSA skills enable candidates to solve coding problems efficiently, optimize solutions, and perform well in technical rounds. While IT companies focus on programming-based problem solving using arrays, linked lists, stacks, queues, trees, graphs, and algorithms like sorting and searching, core companies may also test logical thinking, optimization, and basic algorithmic knowledge relevant to their domain.',
                    resources : [
                        { title: 'DSA Notes', link: 'https://drive.google.com/drive/folders/1C66y2iQhGbzFKdqlSllUVG-V6DHo_O3_' }
                    ],
                    keyTopics: [
                        'Practice coding problems regularly using the “Take U Forward” DSA datasheet, which provides a structured approach to mastering all key topics efficiently.',
                        'Join CodeChef weekly contests to practice DSA and boost your problem-solving skills for placements.',
                        'Maintain an active coding profile on platforms like LeetCode, GeeksforGeeks, or HackerRank, and include it in your resume.',
                        'NeetCode offers the NeetCode 150, a curated set of essential DSA problems with detailed video explanations and step-by-step solutions, along with structured study plans to efficiently prepare for coding interviews.'
                    ],
                    videos: [
                        { title: 'DSA - Take U Forward', link: 'https://www.youtube.com/watch?v=0bHoB32fuj0&list=PLgUwDviBIf0oF6QL8m22w1hIDC1vJ_BHz' }
                    ],
                    additionalResources: [
                        { title: 'Take U Forward A2z Sheet', link: 'https://takeuforward.org/strivers-a2z-dsa-course/strivers-a2z-dsa-course-sheet-2', description: 'Try to complete all the problems' },
                        { title: 'Neetcode', link: 'https://neetcode.io/', description: 'Neetcode provides a more structured way to complete DSA'}
                    ]
                },
                'OOPS': {
                    overview: 'Object-Oriented Programming (OOP) is a programming paradigm that uses "objects" to design software. It is essential for understanding how to structure code in a way that is modular, reusable, and easier to maintain. OOP concepts are widely used in software development and are important for both IT and core placements.',
                    resources: [],
                    keyTopics: [
                        'Focus on core OOP concepts like classes, objects, inheritance, polymorphism, encapsulation, and abstraction.',
                        'Understand practical applications of OOP concepts in real-world scenarios, as this is often discussed in interviews.'
                    ],
                    videos: [
                        { title: 'OOPs in C++', link: 'https://www.youtube.com/watch?v=oOmbSpOzvYg&list=PLdo5W4Nhv31YU5Wx1dopka58teWP9aCee' },
                        { title: 'OOPs in JAVA', link: 'https://www.youtube.com/playlist?list=PL9gnSGHSqcno1G3XjUbwzXHL8_EttOuKk' },
                        { title: 'OOPs in python', link: 'https://www.youtube.com/watch?v=qiSCMNBIP2g' }
                    ],
                    additionalResources: [
                        { title: 'GeeksforGeeks - OOPs', link: 'https://www.geeksforgeeks.org/object-oriented-programming-oops-concept-in-java/', description: '' }
                    ]
                },
                'DATABASE MANAGEMENT SYSTEM': {
                    overview: 'Database Management Systems (DBMS) are essential for managing and organizing data in various applications. Understanding DBMS concepts is crucial for both IT and core placements, as many companies require knowledge of database design, SQL queries, and data manipulation.',
                    resources: [
                        { title: 'DBMS Notes', link: 'https://www.notion.so/DBMS-12af8404adab80019415c7c304e708b3?source=copy_link' }
                    ],
                    keyTopics: [
                        'SQL Practice: Focus on writing queries, joins, aggregations, and subqueries, as many IT interviews include SQL-based questions.',
                        'Core Concepts: Understand normalization, relationships, indexing, and transactions, which are commonly discussed in both technical and HR interviews.',
                        'It is better if you learn advanced topics like Views and Window Functions to handle complex queries effectively.'
                    ],
                    videos: [
                        { title: 'DBMS Free course by Scaler Topics', link: 'https://www.scaler.com/topics/dbms/' },
                        { title: 'DBMS Tutorial for Beginners - Gate Smashers', link: 'https://www.youtube.com/playlist?list=PLxCzCOWd7aiFAN6I8CuViBuCdJgiOkT2Y' }
                    ],
                    additionalResources: [
                        { title: 'GFG - Article', link: 'https://www.geeksforgeeks.org/dbms/dbms/', description: '' },
                        { title: 'LeetCode Database Problems', link: 'https://leetcode.com/problemset/database/', description: 'Practice SQL problems on LeetCode.' }
                    ]
                },
                'OPERATING SYSTEM': {
                    overview: 'Operating Systems (OS) are fundamental for understanding how software interacts with hardware. Knowledge of OS concepts is important for both IT and core placements, as it helps in understanding system-level programming, resource management, and process scheduling.',
                    resources: [],
                    keyTopics: [
                        'Focus on core concepts like processes, threads, memory management, file systems, and scheduling algorithms.',
                        'Understand practical applications of OS concepts in real-world scenarios, as this is often discussed in interviews.',
                        'OS as a course closly related Embedded systems so concertarate on Embedded systems also'
                    ],
                    videos: [
                        { title: 'Free OS course by scaler topics', link: 'https://www.scaler.com/topics/operating-system/' },
                        { title: 'Operating System Full Course - Gate Smashers', link: 'https://www.youtube.com/playlist?list=PLxCzCOWd7aiGz9donHRrE9I3Mwn6XdP8p' },
                        { title: 'Operating System Concepts - Neso Academy', link: 'https://www.youtube.com/watch?v=vBURTt97EkA&list=PLBlnK6fEyqRiVhbXDGLXDk_OQAeuVcp2O' }
                    ],
                    additionalResources: [
                        { title: 'GFG - Article', link: 'https://www.geeksforgeeks.org/operating-systems/', description: '' },
                        { title: 'Prepinsta', link: 'https://prepinsta.com/operating-systems/https://www.geeksforgeeks.org/operating-systems/', description: '' }
                    ]
                },
                'COMPUTER NETWORKS': {
                    overview: 'Computer Networks are essential for understanding how data is transmitted and received across different systems. Knowledge of networking concepts is important for both IT and core placements, as it helps in understanding protocols, network architecture, and security.',
                    resources: [],
                    keyTopics: [
                        'Focus on core concepts like OSI model, TCP/IP, routing, switching, and network security.',
                        'Understand practical applications of networking concepts in real-world scenarios, as this is often discussed in interviews.'
                    ],
                    videos: [
                        { title: 'Free Computer Networks course by Scaler Topics', link: 'https://www.scaler.com/topics/computer-network/' },
                        { title: 'Computer Networks Full Course - Gate Smashers', link: 'https://www.youtube.com/playlist?list=PLxCzCOWd7aiGFBD2-2joCpWOLUrDLvVV_' }
                    ],
                    additionalResources: [
                        { title: 'GFG - Article', link: 'https://www.geeksforgeeks.org/computer-network-tutorials/', description: '' },
                        { title: 'Prepinsta', link: 'https://prepinsta.com/computer-networks/', description: '' }
                    ]
                },
                'COMPUTER ARCHITECTURE AND ORGANIZATION': {
                    overview: 'Computer Architecture and Organization is crucial for understanding the internal workings of computer systems. It covers topics like CPU design, memory hierarchy, instruction sets, and data paths. This knowledge is important for both IT and core placements, as it helps in understanding how software interacts with hardware and optimizes performance.',
                    resources: [],
                    keyTopics: [
                        'Focus on core concepts like CPU architecture, memory organization, instruction sets, and pipelining.',
                        'Companies like NVIDIA, Qualcomm, ARM, and AMD often ask questions on Computer Architecture and Organization'
                    ],
                    videos: [
                        { title: 'Computer Organization and Architecture - Engineering Funda', link: 'https://www.youtube.com/playlist?list=PLgwJf8NK-2e7XZXcFujMw--IDZ2nnsXNT' },
                        { title: 'Computer Organization and Architecture - Neso Academy', link: 'https://www.youtube.com/playlist?list=PLBlnK6fEyqRgLLlzdgiTUKULKJPYc0A4q' }
                    ],
                    additionalResources: [
                        { title: 'Computer System and Architecture by Morris Mano Book', link: 'https://file.notion.so/f/f/68f8d12e-69fe-4008-999d-24754e9df119/72f6f2a4-dd04-4e84-becd-9969adb74b2a/Computer_System_Architecture_(M._Morris_Mano)_(Z-Library).pdf?table=block&id=4af2d43c-9d13-4ad1-b85a-2505a28b5374&spaceId=68f8d12e-69fe-4008-999d-24754e9df119&expirationTimestamp=1759680000000&signature=YV5DJCHgymqLw0lh5tRDzR-Il03z3UiDotQWeawh0hs&downloadName=Computer+System+Architecture+%28M.+Morris+Mano%29+%28Z-Library%29.pdf', description: '' },
                        { title: 'GFG - Article', link: 'https://www.geeksforgeeks.org/computer-organization-architecture/computer-organization-and-architecture-tutorials/', description: '' }
                    ]
                },
                'DEVELOPMENT': {
                    overview: 'Development is a key area in the software industry that involves designing, building, and maintaining applications. It can be broadly categorized into web development, app development, and software development. Learning popular tech stacks like MERN (MongoDB, Express.js, React, Node.js) or Java Spring Boot helps in creating full-fledged applications. A good developer should also understand databases (SQL and NoSQL), version control (Git/GitHub), and basic deployment concepts, as these skills are highly valued in placements and real-world projects.',
                    resources: [],
                    keyTopics: [
                        'MERN Stack - HTML, CSS, JavaScript, Node.js, Express.js, for more advance level learn React.js',
                        'Java Tech Stack - Java, Spring Boot, Maven, for more advance level learn Angular',
                        'Databases - MySQL, MongoDB',
                        'API - RESTful APIs',
                        'Version Control - Git and GitHub',
                        'Cloud Basics - AWS, Azure, or Google Cloud'
                    ],
                    videos: [
                        { title: 'Web Development - Full course', link: 'https://www.udemy.com/course/the-complete-web-development-bootcamp/?couponCode=LETSLEARNNOW' },
                        { title: 'AWS - Scaler topics', link: 'https://www.scaler.com/topics/aws/' },
                        { title: 'Version control - Simplilearn', link: 'https://www.youtube.com/watch?v=liwv7Hi68aI' },
                        { title: 'Spring Boot - Full course in Tamil', link: 'https://www.youtube.com/watch?v=52YKZV_Qj3o&t=2472s' }
                    ],
                    additionalResources: []
                },
                'RESUME BUILDING': {
                    overview: 'Resume Building is vital for placements as it’s the first impression for recruiters. Many companies use ATS (Applicant Tracking Systems) to filter resumes, so ensure yours is well-structured, keyword-optimized, and highlights key skills, projects, and achievements clearly.',
                    resources: [],
                    keyTopics: [
                        'Maintain two versions of your resume - one tailored for core and another for software.',
                        'Fine-tune your resume based on the company and job profile for better alignment with their requirements.',
                        'Maintain an ATS score above 70% to increase the chances of your resume passing automated screening systems.',
                        'Use software like Overleaf, Canva, Novoresume, or Microsoft Word.'
                    ],
                    videos: [],
                    additionalResources: [
                        { title: 'Overleaf Resume Templates', link: 'https://www.overleaf.com/gallery/tagged/cv', description: 'LaTeX-based resume templates for professional formatting.' },
                        { title: 'Canva Resume Builder', link: 'https://www.canva.com/resumes/templates/', description: 'Free and easy-to-use resume templates.' },
                        { title: 'Novoresume', link: 'https://novoresume.com/', description: 'Professional resume builder with templates.' }
                    ]
                },
                'PROJECTS': {
                    overview: 'Projects are a crucial part of your resume and interview discussions. They demonstrate your practical skills, problem-solving abilities, and application of theoretical knowledge. Choose projects that align with your career goals and the job profile you are targeting.',
                    resources: [],
                    keyTopics: [
                        'Include 2 to 4 projects in your resume for a balanced portfolio.',
                        'Be thorough with one main project, knowing it in depth from core to implementation, including challenges faced and how you overcame them.',
                        'Customize your project section based on the company’s requirements or domain focus.',
                        'Host your projects on platforms like GitHub or GitLab to showcase your work to potential employers.'
                    ],
                    videos: [],
                    additionalResources: []
                },
                'SELF INTRO': {
                    overview: 'A well-prepared self-introduction is crucial for making a strong first impression in interviews. It sets the tone for the rest of the conversation and helps you stand out from other candidates. A good self-intro should be concise, relevant, and highlight your strengths, experiences, and career goals.',
                    resources: [],
                    keyTopics: [
                        'Structure your introduction with a clear beginning, middle, and end.',
                        'Practice delivering your introduction confidently and naturally.',
                        'Tailor your introduction to the specific job role and company culture.',
                        'Keep it within 1-2 minutes to maintain the interviewer’s interest.'
                    ],
                    videos: [],
                    additionalResources: []
                },
                'GROUP DISCUSSION PREPARATION': {
                    overview: 'Group Discussions (GD) are a common part of the selection process in many companies, especially for roles that require teamwork and communication skills. A well-conducted GD can showcase your ability to articulate ideas, listen to others, and work collaboratively towards a solution.',
                    resources: [],
                    keyTopics: [
                        'Stay updated with current affairs, industry trends, and general knowledge topics.',
                        'Practice active listening and respectful communication during discussions.',
                        'Focus on clarity, brevity, and relevance when contributing to the discussion.',
                        'Work on building confidence and managing group dynamics effectively.'
                    ],
                    videos: [],
                    additionalResources: [
                        { title: 'GD Topics for Placement - GFG', link: 'https://www.geeksforgeeks.org/interview-experiences/gd-topics-for-placement/', description: 'Stay updated with daily current affairs.' },
                        { title: 'Group Discussion Tips', link: 'https://in.indeed.com/career-advice/career-development/group-discussion-tips', description: '' }
                    ]
                },
                'MANAGERIAL AND HR ROUND PREPARATION': {
                    overview: 'The Managerial and HR rounds are critical stages in the interview process, focusing on assessing your fit within the company culture, your interpersonal skills, and your alignment with the organization’s values and goals. These rounds often explore your past experiences, problem-solving abilities, and how you handle various workplace scenarios.',
                    resources: [],
                    keyTopics: [
                        'Prepare for common HR questions like "Tell me about yourself," "What are your strengths and weaknesses?" and "Why do you want to work here?"',
                        'Understand the company’s mission, values, and recent developments to demonstrate your interest and alignment.',
                        'Practice situational and behavioral questions using the STAR (Situation, Task, Action, Result) method to structure your responses effectively.',
                        'Be ready to discuss career goals, and any gaps in your resume honestly and confidently.'
                    ],
                    videos: [],
                    additionalResources: [
                        { title: 'Top HR Interview Questions - GFG', link: 'https://www.geeksforgeeks.org/top-hr-interview-questions-and-answers/', description: '' },
                        { title: 'HR Interview Questions - PrepInsta', link: 'https://prepinsta.com/cgi/interview-questions/hr-interview-questions/', description: '' }
                    ]
                }
                

                // Add other topics here...
            };

            const data = topicContent[topicName] || (aptitudeTopics.includes(topicName) ? sharedAptitudeContent : null);

            if (data) {
                return `
                    <div class="topic-section">
                        <h3><i class="fas fa-info-circle"></i> Overview</h3>
                        <p class="topic-description">${data.overview}</p>
                    </div>
                    ${data.resources && data.resources.length > 0 ? `
                    <div class="topic-section">
                        <h3><i class="fas fa-book"></i> Study Resources</h3>
                        <div class="resource-links">
                            ${data.resources.map(r => `
                                <div class="resource-card">
                                    <h4>${r.title}</h4>
                                    <a href="${r.link}" target="_blank">
                                        <i class="fas fa-file-pdf"></i> View PDF
                                    </a>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                    ` : ''}
                    ${data.keyTopics && data.keyTopics.length > 0 ? `
                    <div class="topic-section">
                        <div class="key-topics">
                            <h4><i class="fas fa-list-check"></i> Key Points</h4>
                            <ul>
                                ${data.keyTopics.map(k => `<li>${k}</li>`).join('')}
                            </ul>
                        </div>
                    </div>
                    ` : ''}
                    ${data.videos && data.videos.length > 0 ? `
                    <div class="topic-section video-section">
                        <h3><i class="fas fa-video"></i> Video Tutorials</h3>
                        <div class="resource-links">
                            ${data.videos.map(v => `
                                <div class="resource-card">
                                    <h4>${v.title}</h4>
                                    <a href="${v.link}" target="_blank">
                                        <i class="fas fa-play-circle"></i> Visit
                                    </a>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                    ` : ''}
                    ${data.additionalResources && data.additionalResources.length > 0 ? `
                    <div class="topic-section">
                        <h3><i class="fas fa-link"></i> Additional Resources</h3>
                        <div class="resource-links">
                            ${data.additionalResources.map(a => `
                                <div class="resource-card">
                                    <h4>${a.title}</h4>
                                    <p>${a.description}</p>
                                    <a href="${a.link}" target="_blank">
                                        <i class="fas fa-link"></i> Visit
                                    </a>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                    ` : ''}
                `;
            }

            // Use specific content if available, otherwise use default template
                return `
                    <div class="topic-section">
                        <h3><i class="fas fa-info-circle"></i> Overview</h3>
                        <p class="topic-description">
                            This section covers important concepts and resources for <strong>${topicName}</strong>. 
                            Below you'll find study materials, video tutorials, and practice resources to help you master this topic.
                        </p>
                    </div>

                    <div class="topic-section">
                        <h3><i class="fas fa-book"></i> Study Resources</h3>
                        <div class="resource-links">
                            <div class="resource-card">
                                <h4>Tutorial Notes</h4>
                                <p>Comprehensive notes covering all fundamental concepts</p>
                                <a href="#" target="_blank">
                                    <i class="fas fa-file-pdf"></i> View PDF
                                </a>
                            </div>
                            <div class="resource-card">
                                <h4>Practice Problems</h4>
                                <p>Solved examples and practice questions</p>
                                <a href="#" target="_blank">
                                    <i class="fas fa-pen"></i> Start Practice
                                </a>
                            </div>
                            <div class="resource-card">
                                <h4>Reference Books</h4>
                                <p>Recommended textbooks and online resources</p>
                                <a href="#" target="_blank">
                                    <i class="fas fa-book-open"></i> View Books
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="topic-section">
                        <div class="key-topics">
                            <h4><i class="fas fa-list-check"></i> Key Topics to Cover</h4>
                            <ul>
                                <li>Fundamental concepts and definitions</li>
                                <li>Important formulas and theorems</li>
                                <li>Practical applications and examples</li>
                                <li>Common interview questions</li>
                                <li>Problem-solving techniques</li>
                            </ul>
                        </div>
                    </div>

                    <div class="topic-section video-section">
                        <h3><i class="fas fa-video"></i> Video Tutorials</h3>
                        <div class="resource-links">
                            <div class="resource-card">
                                <h4>Introduction Video</h4>
                                <p>Basic concepts explained simply</p>
                                <a href="#" target="_blank">
                                    <i class="fas fa-play-circle"></i> Watch on YouTube
                                </a>
                            </div>
                            <div class="resource-card">
                                <h4>Advanced Topics</h4>
                                <p>Deep dive into complex concepts</p>
                                <a href="#" target="_blank">
                                    <i class="fas fa-play-circle"></i> Watch on YouTube
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="topic-section">
                        <h3><i class="fas fa-link"></i> Additional Resources</h3>
                        <div class="resource-links">
                            <div class="resource-card">
                                <h4>Online Course</h4>
                                <p>Complete course with certification</p>
                                <a href="#" target="_blank">
                                    <i class="fas fa-graduation-cap"></i> Enroll Now
                                </a>
                            </div>
                            <div class="resource-card">
                                <h4>Discussion Forum</h4>
                                <p>Ask questions and get help from peers</p>
                                <a href="#" target="_blank">
                                    <i class="fas fa-comments"></i> Join Discussion
                                </a>
                            </div>
                            <div class="resource-card">
                                <h4>Previous Year Questions</h4>
                                <p>Practice with actual placement questions</p>
                                <a href="#" target="_blank">
                                    <i class="fas fa-file-alt"></i> Download PDF
                                </a>
                            </div>
                        </div>
                    </div>
                `;
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('topicModal');
            if (event.target === modal) {
                closeTopicModal();
            }
        }
    </script>
</body>
</html>