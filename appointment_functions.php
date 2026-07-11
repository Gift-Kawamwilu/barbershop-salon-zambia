<?php
/*
 * APPOINTMENT FUNCTIONS - rewritten to match the actual `appointments` table schema:
 * appointment_id, date_created, client_id, employee_id, start_time, end_time_expected, canceled, cancellation_reason
 *
 * NOTE: This table has no service_id column, so appointments are not currently linked
 * to a specific service. Only client, employee, and time range are tracked.
 * Replace the old createAppointment() and getAppointments() in functions.php with these.
 */

function createAppointment($data) {
    global $con;

    try {
        // Expecting $data['appointment_date'] (Y-m-d), $data['appointment_time'] (H:i),
        // and $data['duration_minutes'] (int) from the booking form, since the table
        // stores a start/end timestamp pair rather than separate date/time/service fields.
        $startTime = $data['appointment_date'] . ' ' . $data['appointment_time'] . ':00';

        $durationMinutes = isset($data['duration_minutes']) ? (int)$data['duration_minutes'] : 30;
        $endTime = date('Y-m-d H:i:s', strtotime($startTime) + ($durationMinutes * 60));

        $sql = "INSERT INTO appointments (client_id, employee_id, start_time, end_time_expected, canceled)
                VALUES (?, ?, ?, ?, 0)";
        $stmt = $con->prepare($sql);
        return $stmt->execute([
            $data['user_id'] ?? $data['client_id'] ?? 1,
            $data['employee_id'],
            $startTime,
            $endTime
        ]);
    } catch (PDOException $e) {
        error_log("Appointment creation failed: " . $e->getMessage());
        return false;
    }
}

function getAppointments($filters = []) {
    global $con;

    $sql = "SELECT a.*,
            CONCAT(c.first_name, ' ', c.last_name) as user_name,
            CONCAT(e.first_name, ' ', e.last_name) as staff_name
            FROM appointments a
            LEFT JOIN clients c ON a.client_id = c.client_id
            LEFT JOIN employees e ON a.employee_id = e.employee_id
            WHERE 1=1";
    $params = [];

    if (isset($filters['date']) && $filters['date']) {
        $sql .= " AND DATE(a.start_time) = ?";
        $params[] = $filters['date'];
    }

    if (isset($filters['user_id']) && $filters['user_id']) {
        $sql .= " AND a.client_id = ?";
        $params[] = $filters['user_id'];
    }

    if (isset($filters['canceled']) && $filters['canceled'] !== '') {
        $sql .= " AND a.canceled = ?";
        $params[] = $filters['canceled'];
    }

    $sql .= " ORDER BY a.start_time DESC";

    $stmt = $con->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAppointmentById($id) {
    global $con;
    $sql = "SELECT a.*,
            CONCAT(c.first_name, ' ', c.last_name) as user_name,
            CONCAT(e.first_name, ' ', e.last_name) as staff_name
            FROM appointments a
            LEFT JOIN clients c ON a.client_id = c.client_id
            LEFT JOIN employees e ON a.employee_id = e.employee_id
            WHERE a.appointment_id = ?";
    $stmt = $con->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function updateAppointment($id, $data) {
    global $con;
    try {
        $sql = "UPDATE appointments SET
                client_id = ?,
                employee_id = ?,
                start_time = ?,
                end_time_expected = ?,
                canceled = ?,
                cancellation_reason = ?
                WHERE appointment_id = ?";
        $stmt = $con->prepare($sql);
        return $stmt->execute([
            $data['client_id'],
            $data['employee_id'],
            $data['start_time'],
            $data['end_time_expected'],
            $data['canceled'] ?? 0,
            $data['cancellation_reason'] ?? null,
            $id
        ]);
    } catch (PDOException $e) {
        error_log("Appointment update failed: " . $e->getMessage());
        return false;
    }
}

function deleteAppointment($id) {
    global $con;
    try {
        $stmt = $con->prepare("DELETE FROM appointments WHERE appointment_id = ?");
        return $stmt->execute([$id]);
    } catch (PDOException $e) {
        error_log("Appointment deletion failed: " . $e->getMessage());
        return false;
    }
}
