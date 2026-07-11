<?php
// admin/employee_edit.php - Create or edit an employee
require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/../employee_functions.php'; // TODO: merge into functions.php, then remove this line

$isEdit = isset($_GET['id']);
$employee = [
    'employee_id' => '',
    'first_name' => '',
    'last_name' => '',
    'phone_number' => '',
    'email' => '',
    'department' => 'barbershop',
    'staff_type' => 'barber'
];
$error = '';

if ($isEdit) {
    $existing = getEmployeeById((int)$_GET['id']);
    if (!$existing) {
        header('Location: employees.php');
        exit;
    }
    $employee = $existing;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'first_name' => sanitizeInput($_POST['first_name'] ?? ''),
        'last_name' => sanitizeInput($_POST['last_name'] ?? ''),
        'phone_number' => sanitizeInput($_POST['phone_number'] ?? ''),
        'email' => sanitizeInput($_POST['email'] ?? ''),
        'department' => sanitizeInput($_POST['department'] ?? 'barbershop'),
        'staff_type' => sanitizeInput($_POST['staff_type'] ?? 'barber'),
    ];

    if (empty($data['first_name']) || empty($data['last_name']) || empty($data['phone_number']) || empty($data['email'])) {
        $error = 'Please fill in all required fields.';
        $employee = array_merge($employee, $data);
    } else {
        if ($isEdit) {
            $success = updateEmployee((int)$_POST['employee_id'], $data);
        } else {
            $success = createEmployee($data);
        }

        if ($success) {
            header('Location: employees.php');
            exit;
        } else {
            $error = 'Failed to save employee.';
            $employee = array_merge($employee, $data);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Edit' : 'New' ?> Employee - Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f6f8; }
        .topbar { background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%); color: white; padding: 20px 30px; display: flex; justify-content: space-between; align-items: center; }
        .topbar a { color: white; text-decoration: none; }
        .topbar .nav a { margin-right: 20px; opacity: 0.85; }
        .topbar .nav a:hover { opacity: 1; text-decoration: underline; }
        .container { max-width: 550px; margin: 30px auto; padding: 0 20px; }
        h1 { color: #2c3e50; margin-bottom: 20px; }
        .card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); }
        .error { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; color: #555; font-size: 14px; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px; font-family: inherit; }
        .btn-save { padding: 12px 24px; background: #2c3e50; color: white; border: none; border-radius: 8px; font-size: 15px; cursor: pointer; }
        .btn-cancel { padding: 12px 24px; background: #eee; color: #333; border: none; border-radius: 8px; font-size: 15px; cursor: pointer; text-decoration: none; display: inline-block; margin-left: 10px; }
    </style>
</head>
<body>
    <div class="topbar">
        <div><a href="dashboard.php">✂️ Admin Panel</a></div>
        <div class="nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="appointments.php">Appointments</a>
            <a href="employees.php">Employees</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <h1><?= $isEdit ? 'Edit Employee #' . (int)$employee['employee_id'] : 'New Employee' ?></h1>
        <div class="card">
            <?php if ($error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <?php if ($isEdit): ?>
                    <input type="hidden" name="employee_id" value="<?= (int)$employee['employee_id'] ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($employee['first_name']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($employee['last_name']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="phone_number">Phone Number</label>
                    <input type="text" id="phone_number" name="phone_number" value="<?= htmlspecialchars($employee['phone_number']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($employee['email']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="department">Department</label>
                    <select id="department" name="department">
                        <option value="barbershop" <?= $employee['department'] === 'barbershop' ? 'selected' : '' ?>>Barbershop</option>
                        <option value="salon" <?= $employee['department'] === 'salon' ? 'selected' : '' ?>>Salon</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="staff_type">Staff Type</label>
                    <select id="staff_type" name="staff_type">
                        <option value="barber" <?= $employee['staff_type'] === 'barber' ? 'selected' : '' ?>>Barber</option>
                        <option value="stylist" <?= $employee['staff_type'] === 'stylist' ? 'selected' : '' ?>>Stylist</option>
                        <option value="both" <?= $employee['staff_type'] === 'both' ? 'selected' : '' ?>>Both</option>
                    </select>
                </div>

                <button type="submit" class="btn-save"><?= $isEdit ? 'Save Changes' : 'Create Employee' ?></button>
                <a href="employees.php" class="btn-cancel">Cancel</a>
            </form>
        </div>
    </div>
</body>
</html>
