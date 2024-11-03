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
    $stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE status NOT IN ('completed', 'canceled')");
    return $stmt->fetchColumn();
}

/*function order_create($table_id) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO orders (table_id, status, created_at) VALUES (?, 'active', NOW())");
    $stmt->execute([$table_id]);
    return $pdo->lastInsertId();
}
*/
// Em functions_orders.php
function order_create($table_id) {
    global $pdo;
    
    // Certifique-se de que $table_id seja um valor individual
    if (is_array($table_id)) {
        // Pega o primeiro valor do array ou você pode tratar isso conforme necessário
        $table_id = reset($table_id);
    }
    
    try {
        $pdo->beginTransaction();
        
        // Verificar se a mesa existe e está disponível
        $stmt = $pdo->prepare("SELECT status, group_id FROM tables WHERE id = ?");
        $stmt->execute([$table_id]); // Aqui, garanta que $table_id seja um valor simples
        $table = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$table) {
            throw new Exception("Mesa não encontrada");
        }
        
        // Criar o pedido
        $stmt = $pdo->prepare("
            INSERT INTO orders (table_id, status, total_amount, created_at) 
            VALUES (?, 'active', 0.00, NOW())
        ");
        $stmt->execute([$table_id]);
        $order_id = $pdo->lastInsertId();
        
        // Atualizar status da mesa
        $stmt = $pdo->prepare("UPDATE tables SET status = 'occupied' WHERE id = ?");
        $stmt->execute([$table_id]);
        
        // Se a mesa faz parte de um grupo, atualizar todas as mesas do grupo
        if ($table['group_id']) {
            $stmt = $pdo->prepare("UPDATE tables SET status = 'occupied' WHERE group_id = ?");
            $stmt->execute([$table['group_id']]);
        }
        
        $pdo->commit();
        return $order_id;
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Erro ao criar pedido: " . $e->getMessage());
        throw new Exception("Erro ao criar pedido");
    }
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
    $stmt = $pdo->prepare("
        UPDATE orders 
        SET total_amount = (
            SELECT SUM(oi.quantity * p.price) 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = :order_id
        ) 
        WHERE id = :order_id
    ");
    $stmt->bindValue(':order_id', $order_id, PDO::PARAM_INT);
    $stmt->execute();
}
function update_order_total($order_id) {
    global $pdo;

    // Calcula o novo total somando os itens do pedido
    $stmt = $pdo->prepare("
        SELECT SUM(order_items.quantity * products.price) as new_total
        FROM order_items
        JOIN products ON order_items.product_id = products.id
        WHERE order_items.order_id = :order_id
    ");
    $stmt->bindValue(':order_id', $order_id, PDO::PARAM_INT);
    $stmt->execute();
    $new_total = $stmt->fetchColumn();

    // Atualiza o total do pedido na tabela `orders`
    $stmt = $pdo->prepare("UPDATE orders SET total_amount = :new_total WHERE id = :order_id");
    $stmt->bindValue(':new_total', $new_total, PDO::PARAM_STR);
    $stmt->bindValue(':order_id', $order_id, PDO::PARAM_INT);
    $stmt->execute();
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
/*
function get_paginated_order($offset, $per_page) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM orders ORDER BY table_id DESC LIMIT :offset, :per_page");
    
    // Use bindValue para evitar injeção de SQL
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':per_page', $per_page, PDO::PARAM_INT);

    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
*/
function get_paginated_order($offset, $per_page) {
    global $pdo;
    // Supondo que a coluna de data se chama 'created_at'
    $stmt = $pdo->prepare("SELECT * FROM orders ORDER BY created_at DESC, table_id DESC LIMIT :offset, :per_page");
    
    // Use bindValue para evitar injeção de SQL
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':per_page', $per_page, PDO::PARAM_INT);

    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function remove_item_from_order($order_id, $item_id) {
    global $pdo;

    $stmt = $pdo->prepare("DELETE FROM order_items WHERE order_id = :order_id AND id = :item_id");
    $stmt->bindValue(':order_id', $order_id, PDO::PARAM_INT);
    $stmt->bindValue(':item_id', $item_id, PDO::PARAM_INT);
    $stmt->execute();
}

function remove_order($order_id) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();

        // Primeiro, remove os itens associados ao pedido
        $stmt = $pdo->prepare("DELETE FROM order_items WHERE order_id = :order_id");
        $stmt->bindValue(':order_id', $order_id, PDO::PARAM_INT);
        $stmt->execute();

        // Depois, remove o pedido
        $stmt = $pdo->prepare("DELETE FROM orders WHERE id = :order_id");
        $stmt->bindValue(':order_id', $order_id, PDO::PARAM_INT);
        $stmt->execute();

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        return false;
    }
}

// function_orders.php
function cancel_order($order_id) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        error_log("Iniciando o cancelamento do pedido $order_id");

        // Atualiza o status do pedido para "cancelled"
        $stmt = $pdo->prepare("UPDATE orders SET status = 'canceled' WHERE id = :order_id");
        $stmt->bindValue(':order_id', $order_id, PDO::PARAM_INT);
        $stmt->execute();
        error_log("Status do pedido atualizado para 'cancelled' para o pedido $order_id");

        // Verifica se o pedido tem uma mesa associada
        $stmt = $pdo->prepare("SELECT table_id FROM orders WHERE id = :order_id");
        $stmt->bindValue(':order_id', $order_id, PDO::PARAM_INT);
        $stmt->execute();
        $order = $stmt->fetch();

        if ($order && $order['table_id']) {
            // Libera a mesa associada ao pedido
            $stmt = $pdo->prepare("UPDATE tables SET status = 'free' WHERE id = :table_id");
            $stmt->bindValue(':table_id', $order['table_id'], PDO::PARAM_INT);
            $stmt->execute();
            error_log("Mesa liberada para o pedido $order_id");
        }

        $pdo->commit();
        error_log("Pedido $order_id cancelado com sucesso");
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Erro ao cancelar pedido $order_id: " . $e->getMessage()); // Log detalhado
        return false;
    }
}
