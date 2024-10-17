<?php

function update_table($id, $number, $status) {
    global $pdo;
    
    $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
    $number = filter_var($number, FILTER_SANITIZE_NUMBER_INT);
    $status = filter_var($status, FILTER_SANITIZE_STRING);
    
    $stmt = $pdo->prepare("UPDATE tables SET number = ?, status = ? WHERE id = ?");
    return $stmt->execute([$number, $status, $id]);
}
function update_table_status($table_id, $status) {
    global $pdo;
    
    // Validate status
    $valid_statuses = ['free', 'occupied'];
    if (!in_array($status, $valid_statuses)) {
        throw new InvalidArgumentException("Invalid status. Must be 'free' or 'occupied'.");
    }
    
    // Verifica o status atual da mesa
    $stmt = $pdo->prepare("SELECT status FROM tables WHERE id = ?");
    $stmt->execute([$table_id]);
    $current_status = $stmt->fetchColumn();
    
    if ($current_status === false) {
        throw new Exception("No table found with ID $table_id.");
    }
    
    // Se o status atual for o mesmo que o novo status, não faz nada
    if ($current_status === $status) {
        throw new Exception("The status of table ID $table_id is already '$status'.");
    }

    $stmt = $pdo->prepare("UPDATE tables SET status = ? WHERE id = ?");
    $stmt->execute([$status, $table_id]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception("No table found with ID $table_id or status was not changed.");
    }
}

function delete_table($id) {
    global $pdo;
    $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
    $stmt = $pdo->prepare("DELETE FROM tables WHERE id = ?");
    return $stmt->execute([$id]);
}
function get_all_tables() {
    global $pdo;
    $stmt = $pdo->query("
        SELECT t.*, 
               CASE 
                   WHEN o.id IS NOT NULL THEN 'com_pedido'
                   WHEN t.status = 'occupied' THEN 'ocupada'
                   ELSE 'livre'
               END AS real_status
        FROM tables t
        LEFT JOIN orders o ON t.id = o.table_id AND o.status = 'active'
        ORDER BY t.number
    ");
    return $stmt->fetchAll();
}
// Funções de Mesas (Tables)
function table_create($number, $capacity, $status = 'free') {
    global $pdo;
    
    $number = filter_var($number, FILTER_SANITIZE_NUMBER_INT);
    $capacity = filter_var($capacity, FILTER_SANITIZE_NUMBER_INT);
    $status = filter_var($status, FILTER_SANITIZE_STRING);
    
    $stmt = $pdo->prepare("INSERT INTO tables (number, capacity, status) VALUES (?, ?, ?)");
    return $stmt->execute([$number, $capacity, $status]);
}

function table_get_by_id($table_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM tables WHERE id = ?");
    $stmt->execute([$table_id]);
    return $stmt->fetch();
}
