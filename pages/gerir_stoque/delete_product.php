<?php
require_once '../../config/config.php';  
require_login();

function delete_product($id) {
    global $pdo;

    try {
        // Verifica dependência na tabela `order_items`
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE product_id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $orderItemsCount = $stmt->fetchColumn();

        // Verifica dependência na tabela `sale_items`
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM sale_items WHERE product_id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $saleItemsCount = $stmt->fetchColumn();

        if ($orderItemsCount > 0 || $saleItemsCount > 0) {
            // Existe dependência em order_items ou sale_items
            return ['success' => false, 'message' => 'Produto está associado a pedidos ou vendas e não pode ser excluído.'];
        }

        // Exclui o produto se não houver dependência
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        if ($stmt->execute()) {
            return ['success' => true];
        } else {
            return ['success' => false, 'message' => 'Erro ao tentar excluir o produto.'];
        }
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Erro no banco de dados: ' . $e->getMessage()];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? 0;

    if ($id > 0) {
        $response = delete_product($id);
        echo json_encode($response);
    } else {
        echo json_encode(['success' => false, 'message' => 'ID do produto inválido.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método inválido.']);
}
