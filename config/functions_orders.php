<?php

// Funções de Pedidos
function get_all_orders() {
    global $pdo;
    $stmt = $pdo->query("SELECT o.*, t.number as table_number FROM orders o JOIN tables t ON o.table_id = t.id ORDER BY o.created_at DESC");
    return $stmt->fetchAll();
}

function get_total_orders_today() {
    global $pdo;
    $stmt = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE DATE(created_at) = CURDATE() AND status = 'completed'");
    return $stmt->fetchColumn() ?: 0;
}
/*if (!function_exists('get_total_sales_today')) {
    function get_total_sales_today() {
        global $pdo; // Acessando a conexão global
        $stmt = $pdo->query("SELECT SUM(total_amount) FROM sales WHERE DATE(sale_date) = CURDATE() AND status = 'completed'");
        return $stmt->fetchColumn() ?: 0;
    }
}*/

function get_open_orders() {
    global $pdo;
    $stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE status != 'completed'");
    return $stmt->fetchColumn();
}
// Funções de Mesas
function create_table($number, $status) {
    global $pdo;
    
    $number = filter_var($number, FILTER_SANITIZE_NUMBER_INT);
    $status = filter_var($status, FILTER_SANITIZE_STRING);
    
    $stmt = $pdo->prepare("INSERT INTO tables (number, status) VALUES (?, ?)");
    return $stmt->execute([$number, $status]);
}