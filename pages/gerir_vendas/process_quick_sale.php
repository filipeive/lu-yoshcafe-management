<?php
require_once '../../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método inválido']);
    exit;
}

$items = $_POST['items'] ?? null;
$paymentMethod = $_POST['paymentMethod'] ?? null;

if (!$items || !is_array($items) || !$paymentMethod) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Criar uma nova venda
    $stmt = $pdo->prepare("INSERT INTO sales (total_amount, payment_method, status) VALUES (0, ?, 'completed')");
    $stmt->execute([$paymentMethod]);
    $sale_id = $pdo->lastInsertId();

    $total_amount = 0;
    $sale_items = [];

    foreach ($items as $item) {
        $product_id = filter_var($item['id'], FILTER_SANITIZE_NUMBER_INT);
        $quantity = filter_var($item['quantity'], FILTER_SANITIZE_NUMBER_INT);

        // Obter informações do produto
        $stmt = $pdo->prepare("SELECT name, price, stock_quantity FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();

        if (!$product) {
            throw new Exception('Produto não encontrado');
        }

        if ($product['stock_quantity'] < $quantity) {
            throw new Exception('Estoque insuficiente para o produto ' . $product['name']);
        }

        // Adicionar item à venda
        $stmt = $pdo->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
        $stmt->execute([$sale_id, $product_id, $quantity, $product['price']]);

        // Atualizar o estoque
        $stmt = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
        $stmt->execute([$quantity, $product_id]);

        $item_total = $product['price'] * $quantity;
        $total_amount += $item_total;

        $sale_items[] = [
            'name' => $product['name'],
            'quantity' => $quantity,
            'price' => $product['price'],
            'total' => $item_total
        ];
    }

    // Atualizar o total da venda
    $stmt = $pdo->prepare("UPDATE sales SET total_amount = ? WHERE id = ?");
    $stmt->execute([$total_amount, $sale_id]);

    $pdo->commit();

    $sale_data = [
        'id' => $sale_id,
        'date' => date('Y-m-d H:i:s'),
        'paymentMethod' => $paymentMethod,
        'items' => $sale_items,
        'total' => $total_amount
    ];

    echo json_encode(['success' => true, 'message' => 'Venda realizada com sucesso', 'sale' => $sale_data]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}