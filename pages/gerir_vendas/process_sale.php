<?php
require_once '../../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método inválido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['items']) || !isset($input['paymentMethod'])) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
    exit;
}

$items = $input['items'];
$paymentMethod = $input['paymentMethod'];

try {
    $pdo->beginTransaction();

    // Criar uma nova venda
    $stmt = $pdo->prepare("INSERT INTO sales (total_amount, payment_method, status) VALUES (0, ?, 'completed')");
    $stmt->execute([$paymentMethod]);
    $sale_id = $pdo->lastInsertId();

    $total_amount = 0;

    foreach ($items as $item) {
        $product_id = $item['id'];
        $quantity = $item['quantity'];
        $unit_price = $item['price'];

        // Verificar estoque
        $stmt = $pdo->prepare("SELECT stock_quantity FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();

        if ($product['stock_quantity'] < $quantity) {
            throw new Exception("Estoque insuficiente para o produto ID: $product_id");
        }

        // Adicionar item à venda
        $stmt = $pdo->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
        $stmt->execute([$sale_id, $product_id, $quantity, $unit_price]);

        // Atualizar o estoque
        $stmt = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
        $stmt->execute([$quantity, $product_id]);

        $total_amount += $unit_price * $quantity;
    }

    // Atualizar o total da venda
    $stmt = $pdo->prepare("UPDATE sales SET total_amount = ? WHERE id = ?");
    $stmt->execute([$total_amount, $sale_id]);

    $pdo->commit();

    $receiptUrl = "print_receipt.php?id=$sale_id";
    echo json_encode(['success' => true, 'message' => 'Venda realizada com sucesso', 'receiptUrl' => $receiptUrl]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}