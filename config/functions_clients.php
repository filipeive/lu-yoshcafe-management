<?php
// Funções de Clientes
function create_client($name, $email, $phone, $address) {
    global $pdo;
    
    $name = filter_var($name, FILTER_SANITIZE_STRING);
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    $phone = filter_var($phone, FILTER_SANITIZE_STRING);
    $address = filter_var($address, FILTER_SANITIZE_STRING);
    
    $stmt = $pdo->prepare("INSERT INTO clients (name, email, phone, address) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$name, $email, $phone, $address]);
}

function get_all_clients() {
    global $pdo;
    /*header('Location: pages/dashboard.php');
    exit;*/
    $stmt = $pdo->query("SELECT * FROM clients ORDER BY name");
    return $stmt->fetchAll();
}

function get_client_by_id($id) {
    global $pdo;
    $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function update_client($id, $name, $email, $phone, $address) {
    global $pdo;
    
    $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
    $name = filter_var($name, FILTER_SANITIZE_STRING);
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    $phone = filter_var($phone, FILTER_SANITIZE_STRING);
    $address = filter_var($address, FILTER_SANITIZE_STRING);
    
    $stmt = $pdo->prepare("UPDATE clients SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
    return $stmt->execute([$name, $email, $phone, $address, $id]);
}

function delete_client($id) {
    global $pdo;
    $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
    $stmt = $pdo->prepare("DELETE FROM clients WHERE id = ?");
    return $stmt->execute([$id]);
}