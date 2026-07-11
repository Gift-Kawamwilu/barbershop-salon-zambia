<?php
// functions.php - Complete functions file for barbershop/salon hybrid system

// Database connection
require_once __DIR__ . '/../../connect.php';

// ============ SESSION FUNCTIONS ============

function isUserLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function getUserFullName($user) {
    return $user['first_name'] . ' ' . $user['last_name'];
}

// ============ SERVICE FUNCTIONS ============

function getServicesByCategory($categoryId = null, $type = null) {
    global $con;
    
    $sql = "SELECT * FROM services WHERE is_active = 1 OR is_active IS NULL";
    $params = [];
    
    if ($categoryId) {
        $sql .= " AND category_id = ?";
        $params[] = $categoryId;
    }
    
    if ($type === 'barbershop') {
        $sql .= " AND (is_barbershop = 1 OR is_barbershop IS NULL)";
    } elseif ($type === 'salon') {
        $sql .= " AND is_salon = 1";
    }
    
    $sql .= " ORDER BY service_name";
    
    $stmt = $con->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getServiceById($id) {
    global $con;
    $stmt = $con->prepare("SELECT * FROM services WHERE service_id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// ============ STAFF/EMPLOYEE FUNCTIONS ============

function getStaffByType($type = null) {
    global $con;
    
    // Build the full name from first_name and last_name
    $selectSQL = "SELECT *, CONCAT(first_name, ' ', last_name) as full_name FROM employees";
    
    try {
        // First try with 'staff_type' column
        $sql = $selectSQL;
        if ($type) {
            $sql .= " WHERE staff_type = ? OR staff_type = 'both'";
            $sql .= " ORDER BY first_name";
            $stmt = $con->prepare($sql);
            $stmt->execute([$type]);
        } else {
            $sql .= " ORDER BY first_name";
            $stmt = $con->query($sql);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // If 'staff_type' doesn't exist, try 'department'
        try {
            $sql = $selectSQL;
            if ($type) {
                $sql .= " WHERE department LIKE ?";
                $sql .= " ORDER BY first_name";
                $stmt = $con->prepare($sql);
                $stmt->execute(["%$type%"]);
            } else {
                $sql .= " ORDER BY first_name";
                $stmt = $con->query($sql);
            }
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e2) {
            // If all else fails, return all employees
            $sql = $selectSQL . " ORDER BY first_name";
            $stmt = $con->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
}

function getStaffById($id) {
    global $con;
    $stmt = $con->prepare("SELECT *, CONCAT(first_name, ' ', last_name) as full_name FROM employees WHERE employee_id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// ============ USER FUNCTIONS (Using your clients table) ============

function registerUser($firstName, $lastName, $email, $phone, $password) {
    global $con;
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    try {
        $stmt = $con->prepare("INSERT INTO clients (first_name, last_name, client_email, phone_number, password) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$firstName, $lastName, $email, $phone, $hashedPassword]);
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Duplicate entry
            return 'email_exists';
        }
        error_log("Registration failed: " . $e->getMessage());
        return false;
    }
}

function loginUser($email, $password) {
    global $con;
    
    $stmt = $con->prepare("SELECT * FROM clients WHERE client_email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // If password is stored as plain text
        if ($password == $user['password']) {
            return $user;
        }
        // If password is hashed (use this once passwords are hashed on registration)
        // if (password_verify($password, $user['password'])) {
        //     return $user;
        // }
    }
    
    return false;
}

function getUserById($id) {
    global $con;
    $stmt = $con->prepare("SELECT client_id, first_name, last_name, client_email, phone_number, created_at FROM clients WHERE client_id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// function isUserLoggedIn() {
//     return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
// }

// function getUserFullName($user) {
//     return $user['first_name'] . ' ' . $user['last_name'];
// }

// ============ ADMIN FUNCTIONS ============

function authenticateAdmin($username, $password) {
    global $con;
    $stmt = $con->prepare("SELECT * FROM barber_admin WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin && sha1($password) === $admin['password']) {
        return $admin;
    }
    return false;
}

function getDashboardStats() {
    global $con;
    $stats = [];
    
    // Total users (if table exists)
    try {
        $stmt = $con->query("SELECT COUNT(*) as count FROM users");
        $stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    } catch (PDOException $e) {
        $stats['total_users'] = 0;
    }
    
    // Total appointments
    try {
        $stmt = $con->query("SELECT COUNT(*) as count FROM appointments");
        $stats['total_appointments'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    } catch (PDOException $e) {
        $stats['total_appointments'] = 0;
    }
    
    // Today's appointments
    try {
        $stmt = $con->prepare("SELECT COUNT(*) as count FROM appointments WHERE appointment_date = CURDATE()");
        $stmt->execute();
        $stats['today_appointments'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    } catch (PDOException $e) {
        $stats['today_appointments'] = 0;
    }
    
    // Pending appointments
    try {
        $stmt = $con->query("SELECT COUNT(*) as count FROM appointments WHERE status = 'pending'");
        $stats['pending_appointments'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    } catch (PDOException $e) {
        $stats['pending_appointments'] = 0;
    }
    
    return $stats;
}

// ============ VALIDATION FUNCTIONS ============

function validateAppointmentData($data) {
    $errors = [];
    
    if (empty($data['service_id']) || !is_numeric($data['service_id'])) {
        $errors[] = "Please select a valid service.";
    }
    
    if (empty($data['employee_id']) || !is_numeric($data['employee_id'])) {
        $errors[] = "Please select a valid professional.";
    }
    
    if (empty($data['appointment_date'])) {
        $errors[] = "Please select a date.";
    } else {
        $today = date('Y-m-d');
        if ($data['appointment_date'] < $today) {
            $errors[] = "Appointment date cannot be in the past.";
        }
    }
    
    if (empty($data['appointment_time'])) {
        $errors[] = "Please select a time.";
    }
    
    return $errors;
}

function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// ============ SERVICE CATEGORY FUNCTIONS ============

function getServiceCategories() {
    global $con;
    try {
        $stmt = $con->query("SELECT * FROM service_categories ORDER BY category_name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}
?>