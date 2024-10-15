<?php
require_once '../../config/config.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = $_GET['id'] ?? 0;
    $product = get_product_by_id($id);

    if ($product) {
        echo json_encode($product);
    } else {
        echo json_encode(['error' => 'Produto não encontrado']);
    }
} else {
    echo json_encode(['error' => 'Método inválido']);
}