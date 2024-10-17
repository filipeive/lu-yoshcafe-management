<?php
// Funções de Vendas

// Função para obter todas as vendas
function get_all_sales() {
    global $pdo; // Acessando a conexão global com o banco de dados
    $stmt = $pdo->query("SELECT * FROM sales ORDER BY sale_date DESC");
    return $stmt->fetchAll();
}

// Função para obter o total de vendas do dia
function get_total_sales_today() {
    global $pdo; // Acessando a conexão global
    $stmt = $pdo->query("SELECT SUM(total_amount) FROM sales WHERE DATE(sale_date) = CURDATE() AND status = 'completed'");
    return $stmt->fetchColumn() ?: 0;
}
/*if (!function_exists('get_total_sales_today')) {
    function get_total_sales_today() {
        global $pdo; // Acessando a conexão global
        $stmt = $pdo->query("SELECT SUM(total_amount) FROM sales WHERE DATE(sale_date) = CURDATE() AND status = 'completed'");
        return $stmt->fetchColumn() ?: 0;
    }
}*/

// Função para criar uma venda
function create_sale($total_amount, $payment_method, $status = 'completed') {
    global $pdo; // Acessando a conexão global

    // Sanitização dos dados
    $total_amount = filter_var($total_amount, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $payment_method = filter_var($payment_method, FILTER_SANITIZE_STRING);
    $status = filter_var($status, FILTER_SANITIZE_STRING);

    $stmt = $pdo->prepare("INSERT INTO sales (total_amount, payment_method, status) VALUES (?, ?, ?)");
    return $stmt->execute([$total_amount, $payment_method, $status]);
}

// Função para adicionar itens de venda
function add_sale_item($sale_id, $product_id, $quantity, $unit_price) {
    global $pdo; // Acessando a conexão global

    // Sanitização dos dados
    $sale_id = filter_var($sale_id, FILTER_SANITIZE_NUMBER_INT);
    $product_id = filter_var($product_id, FILTER_SANITIZE_NUMBER_INT);
    $quantity = filter_var($quantity, FILTER_SANITIZE_NUMBER_INT);
    $unit_price = filter_var($unit_price, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

    $stmt = $pdo->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$sale_id, $product_id, $quantity, $unit_price]);
}

// Função para obter itens de uma venda específica
function get_sale_items($sale_id) {
    global $pdo; // Acessando a conexão global

    // Sanitização dos dados
    $sale_id = filter_var($sale_id, FILTER_SANITIZE_NUMBER_INT);

    $stmt = $pdo->prepare("SELECT * FROM sale_items WHERE sale_id = ?");
    $stmt->execute([$sale_id]);
    return $stmt->fetchAll();
}

// Função para obter os detalhes de uma venda
function get_sale($sale_id) {
    global $pdo; // Acessando a conexão global

    // Sanitização dos dados
    $sale_id = filter_var($sale_id, FILTER_SANITIZE_NUMBER_INT);

    $stmt = $pdo->prepare("SELECT * FROM sales WHERE id = ?");
    $stmt->execute([$sale_id]);
    return $stmt->fetch();
}
// Função para editar uma venda
function update_sale($sale_id, $total_amount, $payment_method, $status) {
    global $pdo; // Acessando a conexão global

    // Sanitização dos dados
    $sale_id = filter_var($sale_id, FILTER_SANITIZE_NUMBER_INT);
    $total_amount = filter_var($total_amount, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $payment_method = filter_var($payment_method, FILTER_SANITIZE_STRING);
    $status = filter_var($status, FILTER_SANITIZE_STRING);

    $stmt = $pdo->prepare("UPDATE sales SET total_amount = ?, payment_method = ?, status = ? WHERE id = ?");
    return $stmt->execute([$total_amount, $payment_method, $status, $sale_id]);
}

// Função para cancelar uma venda
function cancel_sale($sale_id) {
    global $pdo; // Acessando a conexão global

    // Sanitização do ID da venda
    $sale_id = filter_var($sale_id, FILTER_SANITIZE_NUMBER_INT);

    // Atualiza o status da venda para "cancelado"
    $stmt = $pdo->prepare("UPDATE sales SET status = 'cancelled' WHERE id = ?");
    return $stmt->execute([$sale_id]);
}
function count_all_sales() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM sales");
    $stmt->execute();
    return $stmt->fetch()['count'];
}

function get_paginated_sales($offset, $per_page) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM sales ORDER BY sale_date DESC LIMIT :offset, :per_page");
    
    // PDO usa bindValue para parâmetros nomeados ou posicionais
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':per_page', $per_page, PDO::PARAM_INT);

    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
