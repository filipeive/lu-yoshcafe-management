<?php
// Funções de Autenticação e Sessão
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function login($username, $password) {
    global $pdo;
    
    $username = filter_var($username, FILTER_SANITIZE_STRING);
    
    $stmt = $pdo->prepare("SELECT id, password, role FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role']; // Adiciona o papel do usuário na sessão
        return true;
    }
    
    return false;
}

function logout() {
    session_unset();
    session_destroy();
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: ../index.php');
        exit;
    }
}

function require_admin() {
    if (!is_logged_in() || $_SESSION['role'] !== 'admin') {
        header('Location: ../index.php');
        exit;
    }
}