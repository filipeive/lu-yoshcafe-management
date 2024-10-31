<?php
require_once '../../config/config.php';
require_login();

header('Content-Type: application/json'); // Define o cabeçalho JSON
ob_clean(); // Remove qualquer conteúdo extra enviado antes

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => "ID do pedido não especificado."]);
    exit;
}

$order_id = (int)$_GET['id'];

if (cancel_order($order_id)) {
    echo json_encode(['success' => true, 'message' => "Pedido cancelado com sucesso."]);
} else {
    echo json_encode(['success' => false, 'error' => "Erro ao cancelar o pedido."]);
}

exit;
?>
