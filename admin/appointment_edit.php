<?php
// admin/appointment_edit.php - Create or edit an appointment
require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/../appointment_functions.php'; // TODO: merge into functions.php, then remove this line
require_once __DIR__ . '/../employee_functions.php'; // TODO: merge into functions.php, then remove this line

$isEdit = isset($_GET['id']);
$appointment = [
    'appointment_id' => '',
    'client_id' => '',
    'employee_id' => '',
    'start_time' => '',
    'end_time_expected' => '',
    'canceled' => 0,
    'cancellation_reason' => ''
];
$error = '';

if ($isEdit) {
    $existing = getAppointmentById((int)$_GET['id']);
    if (!$existing) {
        header('Location: appointments.php');
        exit;
    }
    $appointment = $existing;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'client_id' => (int)$_POST['client_id'],
        'employee_id' => (int)$_POST['employee_id'],
        'start_time' => $_POST['start_time'],
        'end_time_expected' => $_POST['end_time_expected'],
        'canceled' => isset($_POST['canceled']) ? 1 : 0,
        'cancellation_reason' => sanitizeInput($_POST['cancellation_reason'] ?? '')
    ];

    if (empty($data['client_id']) || empty($data['employee_id']) || empty($data['start_time']) || empty($data['end_time_expected'])) {
        $error = 'Please fill in all required fields.';
        $appointment = array_merge($appointment, $data);
    } else {
        if ($isEdit) {
            $success = updateAppointment((int)$_POST['appointment_id'], $data);
        } else {
            $stmt = null;
            global $con;
            $sql = "INSERT INTO appointments (client_id, employee_id, start_time, end_time_expected, canceled, cancellation_reason)
                    VALUES (?, ?, ?, ?, ?, ?)";
            try {
                $stmt = $con->prepare($sql);
                $success = $stmt->execute([
                    $data['client_id'], $data['employee_id'], $data['start_time'],
                    $data['end_time_expected'], $data['canceled'], $data['cancellation_reason']
                ]);
            } catch (PDOException $e) {
                error_log("Appointment creation failed: " . $e->getMessage());
                $success = false;
            }
        }

        if ($success) {
            header('Location: appointments.php');
            exit;
        } else {
            $error = 'Failed to save appointment.';
            $appointment = array_merge($appointment, $data);
        }
    }
}

$clients = [];
$employeesList = getAllEmployees();
try {
    global $con;
    $stmt = $con->query("SELECT client_id, first_name, last_name FROM clients ORDER BY first_name");
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $clients = [];
}

function toDatetimeLocal($value) {
    if (empty($value) || $value === '0000-00-00 00:00:00') return '';
    return str_replace(' ', 'T', substr($value, 0, 16));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Edit' : 'New' ?> Appointment - Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f6f8; }
        .topbar { background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%); color: white; padding: 20px 30px; display: flex; justify-content: space-between; align-items: center; }
        .topbar a { color: white; text-decoration: none; }
        .topbar .nav a { margin-right: 20px; opacity: 0.85; }
        .topbar .nav a:hover { opacity: 1; text-decoration: underline; }
        .container { max-width: 600px; margin: 30px auto; padding: 0 20px; }
        h1 { color: #2c3e50; margin-bottom: 20px; }
        .card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); }
        .error { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; color: #555; font-size: 14px; }
        .form-group select, .form-group input[type="datetime-local"], .form-group textarea {
            width: 100%; padding: 10px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px; font-family: inherit;
        }
        .form-group.checkbox { display: flex; align-items: center; gap: 8px; }
        .form-group.checkbox label { margin-bottom: 0; }
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
        <h1><?= $isEdit ? 'Edit Appointment #' . (int)$appointment['appointment_id'] : 'New Appointment' ?></h1>
        <div class="card">
            <?php if ($error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <?php if ($isEdit): ?>
                    <input type="hidden" name="appointment_id" value="<?= (int)$appointment['appointment_id'] ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="client_id">Client</label>
                    <select id="client_id" name="client_id" required>
                        <option value="">-- Select client --</option>
                        <?php foreach ($clients as $c): ?>
                            <option value="<?= (int)$c['client_id'] ?>" <?= ((int)$appointment['client_id'] === (int)$c['client_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['first_name'] . ' ' . $c['last_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="employee_id">Staff</label>
                    <select id="employee_id" name="employee_id" required>
                        <option value="">-- Select staff --</option>
                        <?php foreach ($employeesList as $e): ?>
                            <option value="<?= (int)$e['employee_id'] ?>" <?= ((int)$appointment['employee_id'] === (int)$e['employee_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($e['first_name'] . ' ' . $e['last_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="start_time">Start Time</label>
                    <input type="datetime-local" id="start_time" name="start_time"
                           value="<?= htmlspecialchars(toDatetimeLocal($appointment['start_time'])) ?>" required>
                </div>

                <div class="form-group">
                    <label for="end_time_expected">Expected End Time</label>
                    <input type="datetime-local" id="end_time_expected" name="end_time_expected"
                           value="<?= htmlspecialchars(toDatetimeLocal($appointment['end_time_expected'])) ?>" required>
                </div>

                <div class="form-group checkbox">
                    <input type="checkbox" id="canceled" name="canceled" value="1" <?= $appointment['canceled'] ? 'checked' : '' ?>>
                    <label for="canceled">Canceled</label>
                </div>

                <div class="form-group">
                    <label for="cancellation_reason">Cancellation Reason (optional)</label>
                    <textarea id="cancellation_reason" name="cancellation_reason" rows="3"><?= htmlspecialchars($appointment['cancellation_reason'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn-save"><?= $isEdit ? 'Save Changes' : 'Create Appointment' ?></button>
                <a href="appointments.php" class="btn-cancel">Cancel</a>
            </form>
        </div>
    </div>
</body>
</html>