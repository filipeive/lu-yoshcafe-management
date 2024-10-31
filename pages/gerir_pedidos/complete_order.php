<?php
require_once '../../config/config.php';
require_login();

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$response = ["success" => false, "message" => ""];

if ($order_id > 0) {
    if (order_complete_and_generate_sale($order_id)) {
        $response["success"] = true;
        $response["message"] = "Pedido finalizado e venda gerada com sucesso.";
    } else {
        $response["message"] = "Erro ao finalizar pedido e gerar venda.";
    }
} else {
    $response["message"] = "ID do pedido inválido.";
}

// Define o header como JSON e retorna a resposta
header('Content-Type: application/json');
echo json_encode($response);
//garante o redirecionamento para orders.php
header("Location: ../orders.php");
exit;