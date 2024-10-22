<?php

// Funções de Pedidos (Orders)
function order_get_all() {
    global $pdo;
    $stmt = $pdo->query("SELECT o.*, t.number as table_number FROM orders o JOIN tables t ON o.table_id = t.id ORDER BY o.created_at DESC");
    return $stmt->fetchAll();
}

function order_get_total_today() {
    global $pdo;
    $stmt = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE DATE(created_at) = CURDATE() AND status = 'completed'");
    return $stmt->fetchColumn() ?: 0;
}

function order_get_open_count() {
    global $pdo;
    $stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE status != 'completed'");
    return $stmt->fetchColumn();
}

function order_create($table_id) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO orders (table_id, status, created_at) VALUES (?, 'active', NOW())");
    $stmt->execute([$table_id]);
    return $pdo->lastInsertId();
}

function order_get_by_id($order_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT o.*, t.number as table_number FROM orders o JOIN tables t ON o.table_id = t.id WHERE o.id = ?");
    $stmt->execute([$order_id]);
    return $stmt->fetch();
}

function order_get_items($order_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT oi.*, p.name as product_name, p.price as product_price FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
    $stmt->execute([$order_id]);
    return $stmt->fetchAll();
}

function order_add_item($order_id, $product_id, $quantity) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity) VALUES (?, ?, ?)");
    $stmt->execute([$order_id, $product_id, $quantity]);
    
    // Atualizar o total do pedido
    order_update_total($order_id);
}

function order_update_total($order_id) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE orders SET total_amount = (SELECT SUM(oi.quantity * p.price) FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?) WHERE id = ?");
    $stmt->execute([$order_id, $order_id]);
}

function order_complete_and_generate_sale($order_id) {
    global $pdo;
    
    // Iniciar transação
    $pdo->beginTransaction();
    
    try {
        // Obter informações do pedido
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch();
        
        if (!$order) {
            throw new Exception("Pedido não encontrado.");
        }
        
        // Criar venda
        $stmt = $pdo->prepare("INSERT INTO sales (sale_date, total_amount, payment_method, status) VALUES (NOW(), ?, 'Dinheiro', 'completed')");
        $stmt->execute([$order['total_amount']]);
        $sale_id = $pdo->lastInsertId();
        
        // Obter itens do pedido
        $stmt = $pdo->prepare("SELECT oi.*, p.price as unit_price FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
        $stmt->execute([$order_id]);
        $order_items = $stmt->fetchAll();
        
        // Inserir itens da venda
        $stmt = $pdo->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
        foreach ($order_items as $item) {
            $stmt->execute([$sale_id, $item['product_id'], $item['quantity'], $item['unit_price']]);
        }
        
        // Atualizar status do pedido
        $stmt = $pdo->prepare("UPDATE orders SET status = 'completed' WHERE id = ?");
        $stmt->execute([$order_id]);
        
        // Liberar a mesa
        $stmt = $pdo->prepare("UPDATE tables SET status = 'free' WHERE id = ?");
        $stmt->execute([$order['table_id']]);
        
        // Commit da transação
        $pdo->commit();
        
        return true;
    } catch (Exception $e) {
        // Rollback em caso de erro
        $pdo->rollBack();
        error_log("Erro ao finalizar pedido e gerar venda: " . $e->getMessage());
        return false;
    }
}

// Funções de Produtos (Products)
function product_get_available() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM products WHERE stock_quantity > 0 ORDER BY name");
    return $stmt->fetchAll();
}

// Funções de Vendas (Sales)
function sale_get_total_today() {
    global $pdo;
    $stmt = $pdo->query("SELECT SUM(total_amount) FROM sales WHERE DATE(sale_date) = CURDATE() AND status = 'completed'");
    return $stmt->fetchColumn() ?: 0;
}

function get_paginated_order($offset, $per_page) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM orders ORDER BY table_id DESC LIMIT :offset, :per_page");
    
    // Use bindValue para evitar injeção de SQL
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':per_page', $per_page, PDO::PARAM_INT);

    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
