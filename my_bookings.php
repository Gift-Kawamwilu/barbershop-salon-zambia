<?php
// my_bookings.php - View User's Appointments with Full Details
session_start();
require_once 'Includes/functions/functions.php';

// Check if user is logged in
if (!isUserLoggedIn()) {
    header('Location: login.php?redirect=my_bookings.php');
    exit;
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'] ?? 'User';

// Get all appointments for this user
$appointments = getAppointments(['user_id' => $userId]);

// Get upcoming and past appointments
$upcoming = [];
$past = [];
$today = date('Y-m-d');

foreach ($appointments as $appointment) {
    if ($appointment['appointment_date'] >= $today && $appointment['status'] !== 'cancelled') {
        $upcoming[] = $appointment;
    } else {
        $past[] = $appointment;
    }
}

// Handle appointment cancellation
if (isset($_GET['cancel']) && is_numeric($_GET['cancel'])) {
    $appointmentId = (int)$_GET['cancel'];
    if (cancelAppointment($appointmentId)) {
        $message = "Appointment cancelled successfully!";
        $messageType = "success";
        // Refresh the lists
        $appointments = getAppointments(['user_id' => $userId]);
        $upcoming = [];
        $past = [];
        foreach ($appointments as $appointment) {
            if ($appointment['appointment_date'] >= $today && $appointment['status'] !== 'cancelled') {
                $upcoming[] = $appointment;
            } else {
                $past[] = $appointment;
            }
        }
    } else {
        $message = "Failed to cancel appointment. Please try again.";
        $messageType = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Barbershop & Salon</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            min-height: 100vh;
        }
        
        /* Navigation */
        .nav {
            background: white;
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        .nav-brand {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        .nav-brand span.barber { color: #3498db; }
        .nav-brand span.salon { color: #e91e63; }
        .nav-links {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }
        .nav-links a {
            text-decoration: none;
            color: #555;
            padding: 8px 15px;
            border-radius: 5px;
            transition: all 0.3s;
        }
        .nav-links a:hover {
            background: #f0f0f0;
        }
        .nav-links a.active {
            background: #667eea;
            color: white;
        }
        .nav-links .user-name {
            color: #667eea;
            font-weight: 600;
            padding: 8px 15px;
        }
        .nav-links .logout-link {
            color: #e74c3c;
        }
        .nav-links .logout-link:hover {
            background: #fde8e8;
        }
        
        /* Container */
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        /* Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .page-header h1 {
            color: #333;
            font-size: 28px;
        }
        .page-header h1 span {
            color: #667eea;
        }
        .page-header .subtitle {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }
        .btn-book {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: transform 0.2s;
            display: inline-block;
        }
        .btn-book:hover {
            transform: scale(1.05);
        }
        
        /* Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            text-align: center;
        }
        .stat-card .number {
            font-size: 32px;
            font-weight: bold;
            color: #333;
        }
        .stat-card .label {
            color: #888;
            font-size: 14px;
            margin-top: 5px;
        }
        .stat-card .number.blue { color: #3498db; }
        .stat-card .number.green { color: #27ae60; }
        .stat-card .number.orange { color: #f39c12; }
        .stat-card .number.red { color: #e74c3c; }
        .stat-card .number.purple { color: #9b59b6; }
        
        /* Message */
        .message {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* Sections */
        .section {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            padding: 25px;
            margin-bottom: 30px;
        }
        .section h2 {
            color: #333;
            font-size: 20px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        .section h2 .badge {
            background: #667eea;
            color: white;
            padding: 2px 12px;
            border-radius: 20px;
            font-size: 14px;
            margin-left: 10px;
        }
        
        /* Appointment Cards */
        .appointment-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
        }
        .appointment-card {
            border: 2px solid #e8ecf1;
            border-radius: 12px;
            padding: 20px;
            transition: all 0.3s;
            position: relative;
        }
        .appointment-card:hover {
            border-color: #667eea;
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.1);
        }
        .appointment-card.cancelled {
            opacity: 0.6;
            border-color: #e74c3c;
        }
        .appointment-card.cancelled:hover {
            border-color: #e74c3c;
        }
        .appointment-card .service-name {
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }
        .appointment-card .service-details {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }
        .appointment-card .meta {
            display: flex;
            gap: 15px;
            margin-top: 15px;
            flex-wrap: wrap;
        }
        .appointment-card .meta-item {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #555;
            font-size: 14px;
        }
        .appointment-card .meta-item .icon {
            font-size: 16px;
        }
        .status-badge {
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
            margin-top: 10px;
        }
        .status-badge.pending { background: #fff3cd; color: #856404; }
        .status-badge.confirmed { background: #d1ecf1; color: #0c5460; }
        .status-badge.completed { background: #d4edda; color: #155724; }
        .status-badge.cancelled { background: #f8d7da; color: #721c24; }
        
        .appointment-card .actions {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e8ecf1;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .btn-cancel {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: background 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        .btn-cancel:hover {
            background: #c0392b;
        }
        .btn-view {
            background: #667eea;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: background 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        .btn-view:hover {
            background: #5a67d8;
        }
        .btn-disabled {
            background: #ccc;
            color: #666;
            padding: 8px 20px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: not-allowed;
            display: inline-block;
        }
        
        .no-appointments {
            text-align: center;
            padding: 40px 20px;
            color: #888;
        }
        .no-appointments .big-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        .no-appointments h3 {
            color: #333;
            margin-bottom: 10px;
        }
        .no-appointments p {
            margin-bottom: 15px;
        }
        .no-appointments a {
            color: #667eea;
            font-weight: 600;
            text-decoration: none;
        }
        .no-appointments a:hover {
            text-decoration: underline;
        }
        
        /* Responsive */
        @media (max-width: 600px) {
            .nav {
                flex-direction: column;
                align-items: stretch;
            }
            .nav-links {
                justify-content: center;
            }
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .appointment-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="nav">
        <div class="nav-brand">
            ✂️ <span class="barber">Barber</span><span class="salon">Salon</span>
        </div>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="booking-hybrid.php?type=barbershop">Barbershop</a>
            <a href="booking-hybrid.php?type=salon">Salon</a>
            <a href="my_bookings.php" class="active">My Bookings</a>
            <span class="user-name">👋 <?= htmlspecialchars($userName) ?></span>
            <a href="logout.php" class="logout-link">Logout</a>
            <a href="admin/login.php">Admin</a>
        </div>
    </nav>
    
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1>📋 My <span>Bookings</span></h1>
                <div class="subtitle">View and manage all your appointments</div>
            </div>
            <a href="booking-hybrid.php" class="btn-book">+ Book New Appointment</a>
        </div>
        
        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="number blue"><?= count($appointments) ?></div>
                <div class="label">Total Appointments</div>
            </div>
            <div class="stat-card">
                <div class="number green"><?= count($upcoming) ?></div>
                <div class="label">Upcoming</div>
            </div>
            <div class="stat-card">
                <div class="number orange"><?= count(array_filter($appointments, function($a) { return $a['status'] === 'pending'; })) ?></div>
                <div class="label">Pending</div>
            </div>
            <div class="stat-card">
                <div class="number purple"><?= count(array_filter($appointments, function($a) { return $a['status'] === 'completed'; })) ?></div>
                <div class="label">Completed</div>
            </div>
        </div>
        
        <!-- Message -->
        <?php if (isset($message)): ?>
            <div class="message <?= $messageType ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <!-- Upcoming Appointments -->
        <div class="section">
            <h2>📅 Upcoming Appointments <span class="badge"><?= count($upcoming) ?></span></h2>
            
            <?php if (count($upcoming) > 0): ?>
                <div class="appointment-grid">
                    <?php foreach ($upcoming as $appointment): ?>
                        <div class="appointment-card">
                            <div class="service-name"><?= htmlspecialchars($appointment['service_name'] ?? 'Service') ?></div>
                            <div class="service-details">
                                with <?= htmlspecialchars($appointment['staff_name'] ?? 'Professional') ?>
                            </div>
                            <div class="meta">
                                <span class="meta-item">
                                    <span class="icon">📅</span>
                                    <?= date('l, M d, Y', strtotime($appointment['appointment_date'])) ?>
                                </span>
                                <span class="meta-item">
                                    <span class="icon">⏰</span>
                                    <?= date('h:i A', strtotime($appointment['appointment_time'])) ?>
                                </span>
                            </div>
                            <span class="status-badge <?= $appointment['status'] ?>">
                                <?= ucfirst($appointment['status'] ?? 'pending') ?>
                            </span>
                            <div class="actions">
                                <?php if ($appointment['status'] !== 'cancelled' && $appointment['status'] !== 'completed'): ?>
                                    <a href="?cancel=<?= $appointment['id'] ?>" 
                                       class="btn-cancel" 
                                       onclick="return confirm('Are you sure you want to cancel this appointment?')">
                                        Cancel Appointment
                                    </a>
                                <?php else: ?>
                                    <span class="btn-disabled"><?= ucfirst($appointment['status']) ?></span>
                                <?php endif; ?>
                                <a href="appointment-detail.php?id=<?= $appointment['id'] ?>" class="btn-view">View Details</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-appointments">
                    <div class="big-icon">🎉</div>
                    <h3>No Upcoming Appointments</h3>
                    <p>You don't have any upcoming appointments scheduled.</p>
                    <a href="booking-hybrid.php">Book an appointment now</a>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Past Appointments -->
        <div class="section">
            <h2>📜 Past Appointments <span class="badge"><?= count($past) ?></span></h2>
            
            <?php if (count($past) > 0): ?>
                <div class="appointment-grid">
                    <?php foreach ($past as $appointment): ?>
                        <div class="appointment-card <?= $appointment['status'] === 'cancelled' ? 'cancelled' : '' ?>">
                            <div class="service-name"><?= htmlspecialchars($appointment['service_name'] ?? 'Service') ?></div>
                            <div class="service-details">
                                with <?= htmlspecialchars($appointment['staff_name'] ?? 'Professional') ?>
                            </div>
                            <div class="meta">
                                <span class="meta-item">
                                    <span class="icon">📅</span>
                                    <?= date('l, M d, Y', strtotime($appointment['appointment_date'])) ?>
                                </span>
                                <span class="meta-item">
                                    <span class="icon">⏰</span>
                                    <?= date('h:i A', strtotime($appointment['appointment_time'])) ?>
                                </span>
                            </div>
                            <span class="status-badge <?= $appointment['status'] ?>">
                                <?= ucfirst($appointment['status'] ?? 'pending') ?>
                            </span>
                            <div class="actions">
                                <span class="btn-disabled">Completed</span>
                                <?php if ($appointment['status'] === 'completed'): ?>
                                    <a href="#" class="btn-view" style="background: #27ae60;">Leave Review</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-appointments">
                    <div class="big-icon">📭</div>
                    <h3>No Past Appointments</h3>
                    <p>You haven't completed any appointments yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>