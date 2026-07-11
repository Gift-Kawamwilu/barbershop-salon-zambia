<?php
// booking-hybrid.php - Secure booking page with validation
session_start();
require_once 'includes/functions/functions.php';

// Sanitize GET parameter
$serviceType = isset($_GET['type']) ? sanitizeInput($_GET['type']) : 'barbershop';
if (!in_array($serviceType, ['barbershop', 'salon'])) {
    $serviceType = 'barbershop';
}

$pageTitle = ($serviceType === 'salon') ? 'Salon & Styling' : 'Barbershop';

// Get services based on type
if ($serviceType === 'salon') {
    $services = getServicesByCategory(null, 'salon');
    $staff = getStaffByType('stylist');
} else {
    $services = getServicesByCategory(null, 'barbershop');
    $staff = getStaffByType('barber');
}

$message = '';
$messageType = '';
$formData = [
    'service_id' => '',
    'employee_id' => '',
    'appointment_date' => '',
    'appointment_time' => '',
    'notes' => ''
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize all POST data
    $formData = [
        'service_id' => isset($_POST['service_id']) ? (int)sanitizeInput($_POST['service_id']) : 0,
        'employee_id' => isset($_POST['employee_id']) ? (int)sanitizeInput($_POST['employee_id']) : 0,
        'appointment_date' => isset($_POST['appointment_date']) ? sanitizeInput($_POST['appointment_date']) : '',
        'appointment_time' => isset($_POST['appointment_time']) ? sanitizeInput($_POST['appointment_time']) : '',
        'notes' => isset($_POST['notes']) ? sanitizeInput($_POST['notes']) : ''
    ];
    
    // Validate input
    $validationErrors = validateAppointmentData($formData);
    
    if (empty($validationErrors)) {
        // Check if user is logged in
        $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
        
        // If no user is logged in, create a temporary session or redirect to login
        if (!$userId) {
            // For demo: Store appointment data in session to process after login
            $_SESSION['pending_appointment'] = $formData;
            header('Location: login.php?redirect=booking-hybrid.php&type=' . $serviceType);
            exit;
        }
        
        // Create appointment
        $appointmentData = [
            'user_id' => $userId,
            'employee_id' => $formData['employee_id'],
            'service_id' => $formData['service_id'],
            'appointment_date' => $formData['appointment_date'],
            'appointment_time' => $formData['appointment_time'],
            'notes' => $formData['notes']
        ];
        
        $result = createAppointment($appointmentData);
        
        if ($result) {
            $message = '✅ Appointment booked successfully! We will confirm shortly.';
            $messageType = 'success';
            // Clear form data on success
            $formData = array_map(function() { return ''; }, $formData);
        } else {
            $message = '❌ Failed to book appointment. Please try again or contact us directly.';
            $messageType = 'error';
        }
    } else {
        $message = implode('<br>', $validationErrors);
        $messageType = 'error';
    }
}

// Check if there's a pending appointment from login redirect
if (isset($_SESSION['pending_appointment']) && isset($_SESSION['user_id'])) {
    $pendingData = $_SESSION['pending_appointment'];
    $pendingData['user_id'] = (int)$_SESSION['user_id'];
    
    $result = createAppointment($pendingData);
    if ($result) {
        $message = '✅ Appointment booked successfully! We will confirm shortly.';
        $messageType = 'success';
    }
    unset($_SESSION['pending_appointment']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> Booking</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            min-height: 100vh;
        }
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
        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .card {
            background: white;
            padding: 35px;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 25px;
        }
        .message {
            padding: 12px 20px;
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
        .type-selector {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
        }
        .type-btn {
            flex: 1;
            padding: 12px;
            text-align: center;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s;
            border: 2px solid #e0e0e0;
            color: #555;
            background: white;
        }
        .type-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .type-btn.active-barber {
            border-color: #3498db;
            background: #3498db;
            color: white;
        }
        .type-btn.active-salon {
            border-color: #e91e63;
            background: #e91e63;
            color: white;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #555;
        }
        .form-group select,
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            transition: border-color 0.3s;
        }
        .form-group select:focus,
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        .form-group select.error,
        .form-group input.error {
            border-color: #dc3545;
        }
        .error-text {
            color: #dc3545;
            font-size: 13px;
            margin-top: 5px;
            display: block;
        }
        .services-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
            margin-top: 10px;
        }
        .service-card {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 15px;
            transition: all 0.3s;
            cursor: pointer;
        }
        .service-card:hover {
            border-color: #667eea;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
        }
        .service-card.selected {
            border-color: #667eea;
            background: #f8f9ff;
        }
        .service-card .price {
            color: #28a745;
            font-weight: 600;
            font-size: 18px;
        }
        .service-card .duration {
            color: #888;
            font-size: 14px;
        }
        .service-card input[type="radio"] {
            margin-right: 10px;
            width: auto;
        }
        .service-card .service-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        .service-card .service-name {
            font-weight: 600;
            font-size: 16px;
            color: #333;
        }
        .service-card .service-desc {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }
        .btn-submit {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-submit:hover {
            transform: scale(1.02);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .login-prompt {
            background: #fff3cd;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #ffeaa7;
        }
        .login-prompt a {
            color: #667eea;
            font-weight: 600;
        }
        @media (max-width: 600px) {
            .type-selector {
                flex-direction: column;
            }
            .nav {
                flex-direction: column;
                align-items: stretch;
            }
            .nav-links {
                justify-content: center;
            }
            .service-card .service-info {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
    <nav class="nav">
        <div class="nav-brand">
            ✂️ <span class="barber">Barber</span><span class="salon">Salon</span>
        </div>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="booking-hybrid.php?type=barbershop" class="<?= $serviceType === 'barbershop' ? 'active' : '' ?>">Barbershop</a>
            <a href="booking-hybrid.php?type=salon" class="<?= $serviceType === 'salon' ? 'active' : '' ?>">Salon</a>
            <a href="admin/login.php">Admin</a>
        </div>
    </nav>
    
    <div class="container">
        <div class="card">
            <h1>
                <?php if ($serviceType === 'barbershop'): ?>
                    ✂️ Book a Barbershop Service
                <?php else: ?>
                    💇 Book a Salon Service
                <?php endif; ?>
            </h1>
            <p class="subtitle">Choose your service, pick a professional, and select a time.</p>
            
            <!-- Login Prompt -->
            <?php if (!isset($_SESSION['user_id'])): ?>
                <div class="login-prompt">
                    🔐 <a href="login.php?redirect=booking-hybrid.php&type=<?= $serviceType ?>">Login</a> or 
                    <a href="register.php?redirect=booking-hybrid.php&type=<?= $serviceType ?>">Register</a> 
                    to save your bookings and manage appointments.
                </div>
            <?php endif; ?>
            
            <!-- Message -->
            <?php if ($message): ?>
                <div class="message <?= $messageType ?>">
                    <?= $message ?>
                </div>
            <?php endif; ?>
            
            <!-- Service Type Selector -->
            <div class="type-selector">
                <a href="booking-hybrid.php?type=barbershop" 
                   class="type-btn <?= $serviceType === 'barbershop' ? 'active-barber' : '' ?>">
                    ✂️ Barbershop
                </a>
                <a href="booking-hybrid.php?type=salon" 
                   class="type-btn <?= $serviceType === 'salon' ? 'active-salon' : '' ?>">
                    💇 Salon & Styling
                </a>
            </div>
            
            <!-- Booking Form -->
            <form method="POST" id="bookingForm">
                <div class="form-group">
                    <label>Select Service <span style="color:red;">*</span></label>
                    <div class="services-grid">
                        <?php if (count($services) > 0): ?>
                            <?php foreach ($services as $service): ?>
                                <div class="service-card <?= $formData['service_id'] == $service['service_id'] ? 'selected' : '' ?>">
                                    <label>
                                        <div class="service-info">
                                            <div>
                                                <div class="service-name">
                                                    <input type="radio" name="service_id" 
                                                           value="<?= $service['service_id'] ?>" 
                                                           required
                                                           <?= $formData['service_id'] == $service['service_id'] ? 'checked' : '' ?>>
                                                    <?= htmlspecialchars($service['service_name']) ?>
                                                </div>
                                                <div class="service-desc">
                                                    <?= htmlspecialchars($service['service_description'] ?? '') ?>
                                                </div>
                                            </div>
                                            <div>
                                                <span class="price">K <?= number_format($service['service_price'], 2) ?></span>
                                                <span class="duration">• <?= $service['duration_minutes'] ?> min</span>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="color: #666; padding: 20px; text-align: center;">
                                No <?= $serviceType ?> services available at the moment. Please check back later.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Choose Professional <span style="color:red;">*</span></label>
                    <select name="employee_id" required class="<?= isset($validationErrors) && empty($formData['employee_id']) ? 'error' : '' ?>">
                        <option value="">Select a professional...</option>
                        <?php foreach ($staff as $s): ?>
                                <option value="<?= $s['employee_id'] ?>" <?= $formData['employee_id'] == $s['employee_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($s['full_name'] ?? $s['first_name'] . ' ' . $s['last_name']) ?> 
                                    <?php if (isset($s['staff_type'])): ?>
                                        (<?= ucfirst($s['staff_type']) ?>)
                                    <?php endif; ?>
                                </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Date <span style="color:red;">*</span></label>
                    <input type="date" name="appointment_date" 
                           required 
                           min="<?= date('Y-m-d') ?>"
                           class="<?= isset($validationErrors) && empty($formData['appointment_date']) ? 'error' : '' ?>"
                           value="<?= htmlspecialchars($formData['appointment_date']) ?>">
                </div>
                
                <div class="form-group">
                    <label>Time <span style="color:red;">*</span></label>
                    <input type="time" name="appointment_time" 
                           required
                           class="<?= isset($validationErrors) && empty($formData['appointment_time']) ? 'error' : '' ?>"
                           value="<?= htmlspecialchars($formData['appointment_time']) ?>">
                </div>
                
                <div class="form-group">
                    <label>Special Requests</label>
                    <textarea name="notes" placeholder="Any special requests or notes..."><?= htmlspecialchars($formData['notes']) ?></textarea>
                </div>
                
                <button type="submit" class="btn-submit" <?= count($services) === 0 ? 'disabled' : '' ?>>
                    Book Appointment
                </button>
            </form>
        </div>
    </div>
    
    <script>
        // Highlight selected service card
        document.querySelectorAll('input[name="service_id"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.querySelectorAll('.service-card').forEach(card => {
                    card.classList.remove('selected');
                });
                this.closest('.service-card').classList.add('selected');
            });
        });
        
        // Form validation
        document.getElementById('bookingForm').addEventListener('submit', function(e) {
            const service = document.querySelector('input[name="service_id"]:checked');
            const staff = document.querySelector('select[name="employee_id"]');
            const date = document.querySelector('input[name="appointment_date"]');
            const time = document.querySelector('input[name="appointment_time"]');
            
            let errors = [];
            
            if (!service) errors.push('Please select a service.');
            if (!staff.value) errors.push('Please select a professional.');
            if (!date.value) errors.push('Please select a date.');
            if (!time.value) errors.push('Please select a time.');
            
            if (errors.length > 0) {
                e.preventDefault();
                alert('Please fix the following:\n• ' + errors.join('\n• '));
            }
        });
    </script>
</body>
</html>