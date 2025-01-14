<?php
// Funções de Autenticação e Sessão
// Funções de Autenticação e Sessão

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function login($username, $password) {
    global $pdo;
    
    // Sanitiza os inputs
    $username = filter_var($username, FILTER_SANITIZE_STRING);
    
    // Busca o usuário no banco de dados
    $stmt = $pdo->prepare("SELECT id, password, role FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    // Verifica se a senha corresponde
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
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


// Função para verificar se a página é válida
function check_page($requested_page) {
    // Array com páginas válidas
    $valid_pages = ['home', 'tables', 'orders', 'products', 'reports', '404'];

    // Se a página solicitada não for válida, redirecionar para a página 404
    if (!in_array($requested_page, $valid_pages)) {
        header("Location: /404.php");
        exit();
    }
}