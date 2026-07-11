<?php
// admin/appointments.php - List, and delete appointments
require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/../appointment_functions.php'; // TODO: merge into functions.php, then remove this line

$message = '';

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = (int)$_POST['delete_id'];
    if (deleteAppointment($deleteId)) {
        $message = 'Appointment deleted successfully.';
    } else {
        $message = 'Failed to delete appointment.';
    }
}

$appointments = getAppointments();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Appointments - Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f6f8; }
        .topbar {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white; padding: 20px 30px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .topbar a { color: white; text-decoration: none; }
        .topbar .nav a { margin-right: 20px; opacity: 0.85; }
        .topbar .nav a:hover { opacity: 1; text-decoration: underline; }
        .container { max-width: 1100px; margin: 30px auto; padding: 0 20px; }
        h1 { color: #2c3e50; margin-bottom: 20px; }
        .message { background: #d4edda; color: #155724; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.06); }
        th, td { padding: 12px 15px; text-align: left; font-size: 14px; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; color: #555; text-transform: uppercase; font-size: 12px; }
        .status { padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; }
        .status.active { background: #d4edda; color: #155724; }
        .status.canceled { background: #f8d7da; color: #721c24; }
        .actions a, .actions button {
            display: inline-block; padding: 6px 12px; border-radius: 6px; font-size: 13px;
            text-decoration: none; margin-right: 6px; border: none; cursor: pointer;
        }
        .btn-edit { background: #667eea; color: white; }
        .btn-delete { background: #e74c3c; color: white; }
        .add-btn {
            display: inline-block; margin-bottom: 20px; padding: 10px 18px;
            background: #2c3e50; color: white; text-decoration: none; border-radius: 8px; font-size: 14px;
        }
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
        <h1>Manage Appointments</h1>

        <?php if ($message): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <a href="appointment_edit.php" class="add-btn">+ New Appointment</a>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Client</th>
                    <th>Staff</th>
                    <th>Start</th>
                    <th>End (Expected)</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($appointments)): ?>
                    <tr><td colspan="7">No appointments found.</td></tr>
                <?php else: ?>
                    <?php foreach ($appointments as $appt): ?>
                        <tr>
                            <td>#<?= (int)$appt['appointment_id'] ?></td>
                            <td><?= htmlspecialchars($appt['user_name'] ?? 'Unknown') ?></td>
                            <td><?= htmlspecialchars($appt['staff_name'] ?? 'Unassigned') ?></td>
                            <td><?= htmlspecialchars($appt['start_time']) ?></td>
                            <td><?= htmlspecialchars($appt['end_time_expected']) ?></td>
                            <td>
                                <?php if ($appt['canceled']): ?>
                                    <span class="status canceled">Canceled</span>
                                <?php else: ?>
                                    <span class="status active">Active</span>
                                <?php endif; ?>
                            </td>
                            <td class="actions">
                                <a href="appointment_edit.php?id=<?= (int)$appt['appointment_id'] ?>" class="btn-edit">Edit</a>
                                <form method="POST" style="display:inline" onsubmit="return confirm('Delete this appointment?');">
                                    <input type="hidden" name="delete_id" value="<?= (int)$appt['appointment_id'] ?>">
                                    <button type="submit" class="btn-delete">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
