<?php
require_once '../config/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método inválido']);
    exit;
}

$product_id = $_POST['product'] ?? null;
$quantity = $_POST['quantity'] ?? null;

if (!$product_id || !$quantity) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Obter informações do produto
    $stmt = $pdo->prepare("SELECT price, stock_quantity FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        throw new Exception('Produto não encontrado');
    }

    if ($product['stock_quantity'] < $quantity) {
        throw new Exception('Estoque insuficiente');
    }

    // Criar um novo pedido
    $stmt = $pdo->prepare("INSERT INTO orders (total_amount, status) VALUES (?, 'completed')");
    $total_amount = $product['price'] * $quantity;
    $stmt->execute([$total_amount]);
    $order_id = $pdo->lastInsertId();

    // Adicionar item ao pedido
    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity) VALUES (?, ?, ?)");
    $stmt->execute([$order_id, $product_id, $quantity]);

    // Atualizar o estoque
    $stmt = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
    $stmt->execute([$quantity, $product_id]);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Venda realizada com sucesso']);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}