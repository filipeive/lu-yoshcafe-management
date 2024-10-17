<?php
require_once '../../config/config.php';
require_login();

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($order_id > 0) {
    if (order_complete_and_generate_sale($order_id)) {
        $_SESSION['success_message'] = "Pedido finalizado e venda gerada com sucesso.";
    } else {
        $_SESSION['error_message'] = "Erro ao finalizar pedido e gerar venda.";
    }
} else {
    $_SESSION['error_message'] = "ID do pedido inválido.";
}

// Redirecionar de volta para a página de pedidos
header("Location: ../orders.php");
exit;