<?php
require_once '../../config/config.php';
require_login();

if (!isset($_GET['id'])) {
    $_SESSION['error'] = "ID do pedido não especificado.";
    header('Location: ../orders.php');
    exit;
}

$order_id = (int)$_GET['id'];

try {
    $pdo->beginTransaction();
    
    // Get order details first to check if cancellation is possible
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
    
    if (!$order) {
        throw new Exception("Pedido não encontrado.");
    }
    
    if ($order['status'] === 'completed') {
        throw new Exception("Não é possível cancelar um pedido já finalizado.");
    }
    
    // Update order status
    $stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
    $stmt->execute([$order_id]);
    
    // Free up the table if it was occupied
    if ($order['table_id']) {
        $stmt = $pdo->prepare("UPDATE tables SET status = 'free' WHERE id = ?");
        $stmt->execute([$order['table_id']]);
    }
    
    $pdo->commit();
    $_SESSION['success'] = "Pedido cancelado com sucesso.";
    
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "Erro ao cancelar pedido: " . $e->getMessage();
}

header('Location: ../orders.php');
exit;