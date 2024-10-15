<?php

// Funções de Usuários
function create_user($username, $password, $name, $role) {
    global $pdo;
    
    $username = filter_var($username, FILTER_SANITIZE_STRING);
    $name = filter_var($name, FILTER_SANITIZE_STRING);
    $role = filter_var($role, FILTER_SANITIZE_STRING);
    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO users (username, password, name, role) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$username, $hashed_password, $name, $role]);
}

function get_all_users() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM users ORDER BY name");
    return $stmt->fetchAll();
}

function get_user_by_id($id) {
    global $pdo;
    $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function update_user($id, $username, $password, $name, $role) {
    global $pdo;
    
    $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
    $username = filter_var($username, FILTER_SANITIZE_STRING);
    $name = filter_var($name, FILTER_SANITIZE_STRING);
    $role = filter_var($role, FILTER_SANITIZE_STRING);
    
    if ($password) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET username = ?, password = ?, name = ?, role = ? WHERE id = ?");
        return $stmt->execute([$username, $hashed_password, $name, $role, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET username = ?, name = ?, role = ? WHERE id = ?");
        return $stmt->execute([$username, $name, $role, $id]);
    }
}

function delete_user($id) {
    global $pdo;
    $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    return $stmt->execute([$id]);
}