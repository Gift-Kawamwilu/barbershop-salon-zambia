<?php
// admin/dashboard.php - Admin Dashboard
session_start();
require_once '../Includes/functions/functions.php';

if (!function_exists('isAdminLoggedIn')) {
    function isAdminLoggedIn() {
        return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
    }
}

// If not logged in, redirect to login
if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

$adminUsername = $_SESSION['admin_username'] ?? 'Admin';
$stats = getDashboardStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Barbershop & Salon</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f8;
            min-height: 100vh;
        }
        .topbar {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .topbar .brand {
            font-size: 20px;
            font-weight: bold;
        }
        .topbar .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 14px;
        }
        .topbar a.logout {
            color: white;
            background: rgba(255,255,255,0.15);
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            transition: background 0.2s;
        }
        .topbar a.logout:hover {
            background: rgba(255,255,255,0.25);
        }
        .container {
            max-width: 1100px;
            margin: 30px auto;
            padding: 0 20px;
        }
        h1 {
            color: #2c3e50;
            margin-bottom: 25px;
            font-size: 24px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        }
        .stat-card .label {
            color: #888;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        .stat-card .value {
            font-size: 32px;
            font-weight: 700;
            color: #2c3e50;
        }
    </style>
</head>
<body>
    <div class="topbar">
        <div class="brand">✂️ Admin Panel</div>
        <div class="user-info">
            <a href="dashboard.php" style="color:white;text-decoration:none;opacity:0.85;">Dashboard</a>
            <a href="appointments.php" style="color:white;text-decoration:none;opacity:0.85;">Appointments</a>
            <a href="employees.php" style="color:white;text-decoration:none;opacity:0.85;">Employees</a>
            <span>Signed in as <strong><?= htmlspecialchars($adminUsername) ?></strong></span>
            <a href="logout.php" class="logout">Logout</a>
        </div>
    </div>

    <div class="container">
        <h1>Dashboard</h1>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="label">Total Users</div>
                <div class="value"><?= (int)($stats['total_users'] ?? 0) ?></div>
            </div>
            <div class="stat-card">
                <div class="label">Total Appointments</div>
                <div class="value"><?= (int)($stats['total_appointments'] ?? 0) ?></div>
            </div>
            <div class="stat-card">
                <div class="label">Today's Appointments</div>
                <div class="value"><?= (int)($stats['today_appointments'] ?? 0) ?></div>
            </div>
            <div class="stat-card">
                <div class="label">Pending Appointments</div>
                <div class="value"><?= (int)($stats['pending_appointments'] ?? 0) ?></div>
            </div>
        </div>
    </div>
</body>
</html>