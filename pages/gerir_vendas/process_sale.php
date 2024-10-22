<?php
require_once '../../config/config.php';
header('Content-Type: application/json');

// Verificar o método da requisição
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método inválido']);
    exit;
}

// Obter e decodificar os dados de entrada
$input = json_decode(file_get_contents('php://input'), true);

// Validar os dados de entrada
if (!isset($input['items']) || empty($input['items'])) {
    echo json_encode(['success' => false, 'message' => 'Nenhum item na venda']);
    exit;
}

// Extrair os métodos de pagamento
$cashPayment = isset($input['cashPayment']) ? floatval($input['cashPayment']) : 0;
$cardPayment = isset($input['cardPayment']) ? floatval($input['cardPayment']) : 0;
$mpesaPayment = isset($input['mpesaPayment']) ? floatval($input['mpesaPayment']) : 0;
$emolaPayment = isset($input['emolaPayment']) ? floatval($input['emolaPayment']) : 0;

$totalPayment = $cashPayment + $cardPayment + $mpesaPayment + $emolaPayment;

// Determinar o método de pagamento principal
$paymentMethod = 'Múltiplo';
if ($cashPayment > 0 && $cardPayment == 0 && $mpesaPayment == 0 && $emolaPayment == 0) {
    $paymentMethod = 'Dinheiro';
} elseif ($cardPayment > 0 && $cashPayment == 0 && $mpesaPayment == 0 && $emolaPayment == 0) {
    $paymentMethod = 'Cartão';
} elseif ($mpesaPayment > 0 && $cashPayment == 0 && $cardPayment == 0 && $emolaPayment == 0) {
    $paymentMethod = 'M-Pesa';
} elseif ($emolaPayment > 0 && $cashPayment == 0 && $cardPayment == 0 && $mpesaPayment == 0) {
    $paymentMethod = 'Emola';
}

try {
    $pdo->beginTransaction();

    // Criar uma nova venda
    $stmt = $pdo->prepare("INSERT INTO sales (total_amount, payment_method, status, cash_amount, card_amount, mpesa_amount, emola_amount) VALUES (0, ?, 'completed', ?, ?, ?, ?)");
    $stmt->execute([$paymentMethod, $cashPayment, $cardPayment, $mpesaPayment, $emolaPayment]);
    $sale_id = $pdo->lastInsertId();

    $total_amount = 0;

    foreach ($input['items'] as $item) {
        $product_id = $item['id'];
        $quantity = $item['quantity'];
        $unit_price = $item['price'];

        // Verificar estoque
        $stmt = $pdo->prepare("SELECT stock_quantity FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            throw new Exception("Produto não encontrado: ID $product_id");
        }

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

    // Verificar se o pagamento total é suficiente
    if ($totalPayment < $total_amount) {
        throw new Exception("Pagamento insuficiente. Total da venda: $total_amount, Total pago: $totalPayment");
    }

    // Atualizar o total da venda
    $stmt = $pdo->prepare("UPDATE sales SET total_amount = ? WHERE id = ?");
    $stmt->execute([$total_amount, $sale_id]);

    $pdo->commit();

    $receiptUrl = "print_receipt.php?id=$sale_id";
    echo json_encode([
        'success' => true,
        'message' => 'Venda realizada com sucesso',
        'receiptUrl' => $receiptUrl,
        'saleId' => $sale_id,
        'totalAmount' => $total_amount,
        'change' => $totalPayment - $total_amount
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>