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
        
        /* Main Content Styles */
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
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
        <div class="nav-links">
            <a href="placement_experience.php"><i class="fas fa-book"></i> PLACED EXPERIENCE</a>
            <a href="chatbot.php"><i class="fas fa-pencil-alt"></i> PREP WITH AI</a>
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
                <a href="chatbot.php"><i class="fas fa-pencil-alt"></i> PREP WITH AI</a>
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
        <!-- Tree Structure -->
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
                                        <li><span class="toggle" style="visibility: hidden;"></span><span class="content">DIGITAL ELECTRONICS</span></li>
                                        <li><span class="toggle" style="visibility: hidden;"></span><span class="content">DIGITAL SIGNAL PROCESSING</span></li>
                                        <li><span class="toggle" style="visibility: hidden;"></span><span class="content">VERILOG</span></li>
                                        <li><span class="toggle" style="visibility: hidden;"></span><span class="content">VLSI</span></li>
                                        <li><span class="toggle" style="visibility: hidden;"></span><span class="content">CMOS PHYSICS</span></li>
                                        <li><span class="toggle" style="visibility: hidden;"></span><span class="content">STATIC TIMING ANALYSIS</span></li>
                                        <li><span class="toggle" style="visibility: hidden;"></span><span class="content">COMPUTER ARCHITECTURE AND ORGANIZATION</span></li>
                                        <li><span class="toggle" style="visibility: hidden;"></span><span class="content">C</span></li>
                                        <li><span class="toggle" style="visibility: hidden;"></span><span class="content">LOW POWER DESIGN TECHNIQUES</span></li>
                                        <li>
                                            <span class="toggle">+</span>
                                            <span class="content">MISCELLANEOUS</span>
                                            <ul>
                                                <li><span class="toggle" style="visibility: hidden;"></span><span class="content">FIFO DEPTH CALCULATIONS</span></li>
                                                <li><span class="toggle" style="visibility: hidden;"></span><span class="content">STUCK AT FAULT</span></li>
                                                <li><span class="toggle" style="visibility: hidden;"></span><span class="content">CLOCK DOMAIN CROSSING</span></li>
                                                <li><span class="toggle" style="visibility: hidden;"></span><span class="content">ELECTRIC CIRCUIT ANALYSIS</span></li>
                                            </ul>
                                        </li>
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
        <p>Department of EEE - PSG iTech © 2025. All rights reserved.</p>
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
            // Mobile menu functionality
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const mobileMenu = document.getElementById('mobileMenu');
            const mobileMenuClose = document.getElementById('mobileMenuClose');

            mobileMenuBtn.addEventListener('click', function() {
                mobileMenu.classList.add('active');
                document.body.style.overflow = 'hidden';
            });

            mobileMenuClose.addEventListener('click', function() {
                mobileMenu.classList.remove('active');
                document.body.style.overflow = '';
            });

            // Close menu when clicking overlay
            mobileMenu.addEventListener('click', function(e) {
                if (e.target === mobileMenu) {
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
                'ELECTRIC CIRCUIT ANALYSIS':{
                    overview: 'Electric Circuit Analysis is a fundamental subject in electrical engineering that deals with the study of electrical circuits and their behavior. It covers various concepts such as Ohm\'s Law, Kirchhoff\'s Laws, network theorems, transient and steady-state analysis, and AC/DC circuit analysis. Understanding these concepts is crucial for designing and analyzing electrical systems.',
                    resources: [
                        { title: 'CIRCUIT THEORY by Nagoor kani', link: 'https://file.notion.so/f/f/68f8d12e-69fe-4008-999d-24754e9df119/0205bf81-804d-4e23-9699-3488627a26d2/Circuit_Theory_(A._Nagoor_Kani)_(Z-Library).pdf?table=block&id=83d1ba43-00c3-42a1-a6ba-3e915b4a299a&spaceId=68f8d12e-69fe-4008-999d-24754e9df119&expirationTimestamp=1759687200000&signature=i5fL8uWspsnUwBe6-XTpYdx69ZlCMD1Z1Hm4w3CmAmY&downloadName=Circuit+Theory+%28A.+Nagoor+Kani%29+%28Z-Library%29.pdf' },
                        { title: 'Fundamentals of Electric Circuits by Charles Alexander Matthew Sadiku', link: 'https://file.notion.so/f/f/68f8d12e-69fe-4008-999d-24754e9df119/2af659d5-9606-4b8d-be70-d56cd649b3b4/Fundamentals_of_Electric_Circuits_(7th_Ed.)_International_Student_Edition)_(Charles_K._Alexander_Matthew_Sadiku)_(Z-Library).pdf?table=block&id=bc8aeb96-de07-48f8-aeed-10af1e1090bf&spaceId=68f8d12e-69fe-4008-999d-24754e9df119&expirationTimestamp=1759687200000&signature=0mZyhdPfHjA63V0fsHbQm9WKezSAb8pXUjE2bGybhPY&downloadName=Fundamentals+of+Electric+Circuits+%287th+Ed.%29+International+Student+Edition%29+%28Charles+K.+Alexander%2C+Matthew+Sadiku%29+%28Z-Library%29.pdf'}
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
                        { title: 'HandWritten Notes', link: 'https://file.notion.so/f/f/68f8d12e-69fe-4008-999d-24754e9df119/13236107-11fc-4cc0-9172-e4ec9ad439f5/CONTROL_SYSTEM_HANDWRITTEN.pdf?table=block&id=c56fece6-8429-4b49-9902-bdb7ab5c93f4&spaceId=68f8d12e-69fe-4008-999d-24754e9df119&expirationTimestamp=1759687200000&signature=5IPyRIIsgxOOSKtd4R0T_UzEm8YtMmCV3FWwX6JKC1E&downloadName=CONTROL_SYSTEM_HANDWRITTEN.pdf' }
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
                        { title: 'Linear Integrated Circuits by Roy Choudhury', link: 'https://file.notion.so/f/f/68f8d12e-69fe-4008-999d-24754e9df119/8079f3e7-42e7-4f7f-9dc8-e1209da15456/linear_integrated_circuit_applications_(Roy_Choudhary)_(Z-Library).pdf?table=block&id=5e4b0fe8-6adb-4386-904f-2b6c65a22c51&spaceId=68f8d12e-69fe-4008-999d-24754e9df119&expirationTimestamp=1759694400000&signature=6uNtO_IE1f-Bsl8JU6vWJ2dgvCX4WKyDeAgoJ1Dbj2k&downloadName=linear+integrated+circuit+applications+%28Roy+Choudhary%29+%28Z-Library%29.pdf'}
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
                'ELECTRON DEVICES AND CIRCUITS': {
                    overview: 'Electron Devices and Circuits is a fundamental subject in electrical engineering that focuses on the study of semiconductor devices and their applications in electronic circuits. It covers topics such as diodes, transistors, and their configurations, as well as various electronic circuits used in amplifiers, oscillators, and switching applications.',
                    resources: [
                        { title: 'Electronic Devices and Circuits by David A. Bell', link: 'https://file.notion.so/f/f/68f8d12e-69fe-4008-999d-24754e9df119/6be9ecd1-250f-485a-b8d2-a775ac29dddf/Electronic_Devices_and_Circuit_Theory_(11th_Edition)_(Robert_L._Boylestad_Louis_Nashelsky)_(Z-Library).pdf?table=block&id=63e45f21-d68e-4236-ad88-85bd22dcbe37&spaceId=68f8d12e-69fe-4008-999d-24754e9df119&expirationTimestamp=1759694400000&signature=C1Wt3zhoka0Ms0re_oxgwopErK0LF74qx_1pabfO_qU&downloadName=Electronic+Devices+and+Circuit+Theory+%2811th+Edition%29+%28Robert+L.+Boylestad%2C+Louis+Nashelsky%29+%28Z-Library%29.pdf'}                   
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
                        { title: 'Digital Logic and Computer Design by MORRIS MANO', link: 'https://file.notion.so/f/f/68f8d12e-69fe-4008-999d-24754e9df119/748521e6-f447-4168-92ce-9fb3f24feec2/Digital_Logic_and_Computer_Design._(M._Morris_Mano.)_(Z-Library).pdf?table=block&id=ce76cf98-141f-4a0b-b854-ccca02c94487&spaceId=68f8d12e-69fe-4008-999d-24754e9df119&expirationTimestamp=1759680000000&signature=B5QnTPc-O2mnYOBVVJDL7CMoB9cIjSf3WciyZQSCkGU&downloadName=Digital+Logic+and+Computer+Design.+%28M.+Morris+Mano.%29+%28Z-Library%29.pdf' }
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
                        { title: 'VLSI Design Complete Road map', link: 'https://file.notion.so/f/f/68f8d12e-69fe-4008-999d-24754e9df119/b3f163b2-081f-410b-b4a3-dee22bb91618/digital_roadmap.pdf?table=block&id=fc7e5f15-88b9-414a-9849-e67172aa210c&spaceId=68f8d12e-69fe-4008-999d-24754e9df119&expirationTimestamp=1759694400000&signature=9dFqT9QKSxfIJeJA3b2jhm1pCfAFwfOUW--lBuGpo78&downloadName=digital+roadmap.pdf'},
                        { title: 'Handwritten notes', link: 'https://file.notion.so/f/f/68f8d12e-69fe-4008-999d-24754e9df119/bbc7776f-f663-4a46-a6fa-f200ec80fbe0/New_Doc_01-07-2023_21.47.pdf?table=block&id=74e5f7e4-9921-4e0f-82b4-31f0f6c8fea0&spaceId=68f8d12e-69fe-4008-999d-24754e9df119&expirationTimestamp=1759701600000&signature=MdoocCqt6B5GxwKP5lnDasfEkJtIzCikTfdwPNjec48&downloadName=New+Doc+01-07-2023+21.47.pdf'}
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
                        { title: 'Microprocessor (8085) And its Applications by Nagoor kani', link: 'https://file.notion.so/f/f/68f8d12e-69fe-4008-999d-24754e9df119/82941c7a-1efd-456a-a51f-8ced5fa9cd42/Microprocessor_(8085)_And_its_Applications_(A._Nagoor_Kani)_(Z-Library).pdf?table=block&id=9879857b-e244-4f5c-8031-7d1dc973f3ae&spaceId=68f8d12e-69fe-4008-999d-24754e9df119&expirationTimestamp=1759687200000&signature=sBL_m-9SRBF3DJ9SnkqILVMHA9Q9bYwUM5B57zxG9mg&downloadName=Microprocessor+%288085%29+And+its+Applications+%28A.+Nagoor+Kani%29+%28Z-Library%29.pdf' },
                        { title: 'The 8051 Microcontroller and Embedded Systems by Muhammad Ali Mazidi', link: 'https://file.notion.so/f/f/68f8d12e-69fe-4008-999d-24754e9df119/09d2c228-61cb-44b0-8028-bfd090c97c4c/The_8051_Microcontroller_and_Embedded_Systems_(Muhammad_Ali_Mazidi_Janice_G._Mazidi_etc.)_(Z-Library).pdf?table=block&id=44903dd5-0379-424b-9318-1f19c5fbd8f8&spaceId=68f8d12e-69fe-4008-999d-24754e9df119&expirationTimestamp=1759687200000&signature=xoMQd0mwzN-P3-T_wv66nbJXCoENpJjcKiM2Ndlpdgg&downloadName=The+8051+Microcontroller+and+Embedded+Systems+%28Muhammad+Ali+Mazidi%2C+Janice+G.+Mazidi+etc.%29+%28Z-Library%29.pdf'}
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
                        { title: 'C Programming Notes', link: './asset/C_BYTS_HANDWRITTEN.pdf' },
                        { title: 'C BYTS class screenshots', link: './asset/C_programming_Complete_Notes_(1).pdf' },
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
                        { title: 'C++ Programming Notes', link: './asset/Cpp_BYTS_HANDWRITTEN.pdf' }
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
                        { title: 'DSA Notes', link: './asset/DSA_BYTS_HANDWRITTEN.pdf' }
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
                    resources: [],
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