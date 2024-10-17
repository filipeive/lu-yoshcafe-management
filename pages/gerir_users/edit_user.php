<?php
require_once '../../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifica se estamos atualizando um usuário
    if (isset($_POST['id'])) {
        $id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
        $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
        $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
        $password = filter_var($_POST['password'], FILTER_SANITIZE_STRING);
        $role = filter_var($_POST['role'], FILTER_SANITIZE_STRING);

        // Chama a função para atualizar o usuário
        $updated = update_user($id, $username, $password, $name, $role);

        // Retorna o resultado da atualização
        echo json_encode(['success' => $updated]);
    }
    // Verifica se estamos buscando um usuário para edição
    else if (isset($_POST['user_id'])) {
        $user_id = filter_var($_POST['user_id'], FILTER_SANITIZE_NUMBER_INT);
        $user = get_user_by_id($user_id);
        
        if ($user) {
            echo json_encode(['success' => true, 'user' => $user]);
        } else {
            echo json_encode(['success' => false]);
        }
    }
}
