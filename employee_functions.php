<?php
/*
 * EMPLOYEE CRUD FUNCTIONS
 * Matches employees table: employee_id, first_name, last_name, phone_number, email, department, staff_type
 */

function getAllEmployees() {
    global $con;
    $stmt = $con->query("SELECT * FROM employees ORDER BY first_name");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getEmployeeById($id) {
    global $con;
    $stmt = $con->prepare("SELECT * FROM employees WHERE employee_id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function createEmployee($data) {
    global $con;
    try {
        $sql = "INSERT INTO employees (first_name, last_name, phone_number, email, department, staff_type)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $con->prepare($sql);
        return $stmt->execute([
            $data['first_name'],
            $data['last_name'],
            $data['phone_number'],
            $data['email'],
            $data['department'] ?? 'barbershop',
            $data['staff_type'] ?? 'barber'
        ]);
    } catch (PDOException $e) {
        error_log("Employee creation failed: " . $e->getMessage());
        return false;
    }
}

function updateEmployee($id, $data) {
    global $con;
    try {
        $sql = "UPDATE employees SET
                first_name = ?,
                last_name = ?,
                phone_number = ?,
                email = ?,
                department = ?,
                staff_type = ?
                WHERE employee_id = ?";
        $stmt = $con->prepare($sql);
        return $stmt->execute([
            $data['first_name'],
            $data['last_name'],
            $data['phone_number'],
            $data['email'],
            $data['department'] ?? 'barbershop',
            $data['staff_type'] ?? 'barber',
            $id
        ]);
    } catch (PDOException $e) {
        error_log("Employee update failed: " . $e->getMessage());
        return false;
    }
}

function deleteEmployee($id) {
    global $con;
    try {
        $stmt = $con->prepare("DELETE FROM employees WHERE employee_id = ?");
        return $stmt->execute([$id]);
    } catch (PDOException $e) {
        error_log("Employee deletion failed: " . $e->getMessage());
        return false;
    }
}
