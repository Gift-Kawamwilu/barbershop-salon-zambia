<?php
// admin/employees.php - List and delete employees
require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/../employee_functions.php'; // TODO: merge into functions.php, then remove this line

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = (int)$_POST['delete_id'];
    if (deleteEmployee($deleteId)) {
        $message = 'Employee deleted successfully.';
    } else {
        $message = 'Failed to delete employee. They may have existing appointments linked to them.';
    }
}

$employees = getAllEmployees();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Employees - Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f6f8; }
        .topbar { background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%); color: white; padding: 20px 30px; display: flex; justify-content: space-between; align-items: center; }
        .topbar a { color: white; text-decoration: none; }
        .topbar .nav a { margin-right: 20px; opacity: 0.85; }
        .topbar .nav a:hover { opacity: 1; text-decoration: underline; }
        .container { max-width: 1000px; margin: 30px auto; padding: 0 20px; }
        h1 { color: #2c3e50; margin-bottom: 20px; }
        .message { background: #d4edda; color: #155724; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.06); }
        th, td { padding: 12px 15px; text-align: left; font-size: 14px; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; color: #555; text-transform: uppercase; font-size: 12px; }
        .actions a, .actions button { display: inline-block; padding: 6px 12px; border-radius: 6px; font-size: 13px; text-decoration: none; margin-right: 6px; border: none; cursor: pointer; }
        .btn-edit { background: #667eea; color: white; }
        .btn-delete { background: #e74c3c; color: white; }
        .add-btn { display: inline-block; margin-bottom: 20px; padding: 10px 18px; background: #2c3e50; color: white; text-decoration: none; border-radius: 8px; font-size: 14px; }
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
        <h1>Manage Employees</h1>

        <?php if ($message): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <a href="employee_edit.php" class="add-btn">+ New Employee</a>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Department</th>
                    <th>Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($employees)): ?>
                    <tr><td colspan="7">No employees found.</td></tr>
                <?php else: ?>
                    <?php foreach ($employees as $emp): ?>
                        <tr>
                            <td>#<?= (int)$emp['employee_id'] ?></td>
                            <td><?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?></td>
                            <td><?= htmlspecialchars($emp['phone_number']) ?></td>
                            <td><?= htmlspecialchars($emp['email']) ?></td>
                            <td><?= htmlspecialchars($emp['department'] ?? '') ?></td>
                            <td><?= htmlspecialchars($emp['staff_type'] ?? '') ?></td>
                            <td class="actions">
                                <a href="employee_edit.php?id=<?= (int)$emp['employee_id'] ?>" class="btn-edit">Edit</a>
                                <form method="POST" style="display:inline" onsubmit="return confirm('Delete this employee?');">
                                    <input type="hidden" name="delete_id" value="<?= (int)$emp['employee_id'] ?>">
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
