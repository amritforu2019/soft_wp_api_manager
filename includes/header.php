<?php require_once 'includes/auth_check.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>SMS/WhatsApp API Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 260px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding-top: 20px;
            transition: all 0.3s;
            z-index: 1000;
        }
        .sidebar .logo {
            text-align: center;
            color: white;
            padding: 20px;
            font-size: 24px;
            font-weight: 600;
        }
        .sidebar .logo i {
            font-size: 40px;
            display: block;
            margin-bottom: 10px;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            margin: 5px 10px;
            border-radius: 8px;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
        }
        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
        }
        .main-content {
            margin-left: 260px;
            padding: 20px;
            min-height: 100vh;
        }
        .navbar-top {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 15px 30px;
            margin-bottom: 30px;
            border-radius: 10px;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            font-weight: 600;
        }
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-card .icon {
            font-size: 40px;
            opacity: 0.8;
        }
        .badge-status-active { background-color: #28a745; }
        .badge-status-suspended { background-color: #ffc107; }
        .badge-status-expired { background-color: #dc3545; }
        .badge-pending { background-color: #17a2b8; }
        .badge-sent { background-color: #28a745; }
        .badge-failed { background-color: #dc3545; }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <i class="bi bi-chat-dots-fill"></i>
            <div>API Manager</div>
        </div>
        <nav class="nav flex-column">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'customers.php' ? 'active' : ''; ?>" href="customers.php">
                <i class="bi bi-people"></i> Customers
            </a>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'messages.php' ? 'active' : ''; ?>" href="messages.php">
                <i class="bi bi-envelope"></i> Messages
            </a>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'api_logs.php' ? 'active' : ''; ?>" href="api_logs.php">
                <i class="bi bi-journal-text"></i> API Logs
            </a>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'delivery_reports.php' ? 'active' : ''; ?>" href="delivery_reports.php">
                <i class="bi bi-check2-circle"></i> Delivery Reports
            </a>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>" href="settings.php">
                <i class="bi bi-gear"></i> Settings
            </a>
            <hr style="border-color: rgba(255,255,255,0.3);">
            <a class="nav-link" href="logout.php">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="navbar-top">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><?php echo isset($page_title) ? $page_title : 'Dashboard'; ?></h4>
                <div>
                    <i class="bi bi-person-circle"></i>
                    <span class="ms-2">Welcome, <strong><?php echo $_SESSION['admin_name']; ?></strong></span>
                </div>
            </div>
        </div>
