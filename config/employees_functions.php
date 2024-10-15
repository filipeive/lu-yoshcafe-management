
<?php
// Funções de Funcionários
function create_employee($name, $role, $hire_date) {
    global $pdo;
    
    $name = filter_var($name, FILTER_SANITIZE_STRING);
    $role = filter_var($role, FILTER_SANITIZE_STRING);
    $hire_date = filter_var($hire_date, FILTER_SANITIZE_STRING);
    
    $stmt = $pdo->prepare("INSERT INTO employees (name, role, hire_date) VALUES (?, ?, ?)");
    return $stmt->execute([$name, $role, $hire_date]);
}

function get_all_employees() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM employees ORDER BY name");
    return $stmt->fetchAll();
}

function get_employee_by_id($id) {
    global $pdo;
    $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function update_employee($id, $name, $role, $hire_date) {
    global $pdo;
    
    $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
    $name = filter_var($name, FILTER_SANITIZE_STRING);
    $role = filter_var($role, FILTER_SANITIZE_STRING);
    $hire_date = filter_var($hire_date, FILTER_SANITIZE_STRING);
    
    $stmt = $pdo->prepare("UPDATE employees SET name = ?, role = ?, hire_date = ? WHERE id = ?");
    return $stmt->execute([$name, $role, $hire_date, $id]);
}

function delete_employee($id) {
    global $pdo;
    $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
    $stmt = $pdo->prepare("DELETE FROM employees WHERE id = ?");
    return $stmt->execute([$id]);
}