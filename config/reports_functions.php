<?php
// Funções de Relatórios

function get_total_sales() {
    global $pdo;
    $stmt = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE status = 'completed'");
    return $stmt->fetchColumn() ?: 0;
}

function get_total_orders() {
    global $pdo;
    $stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'completed'");
    return $stmt->fetchColumn();
}

function get_total_clients() {
    global $pdo;
    $stmt = $pdo->query("SELECT COUNT(*) FROM clients");
    return $stmt->fetchColumn();
}

function get_table_by_id($id) {
    global $pdo;
    $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
    $stmt = $pdo->prepare("SELECT * FROM tables WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Função para obter vendas mensais
function get_monthly_sales() {
    global $pdo;
    $stmt = $pdo->query("
        SELECT DATE_FORMAT(sale_date, '%Y-%m') AS month, SUM(total_amount) AS total
        FROM sales
        WHERE sale_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
        GROUP BY month
        ORDER BY month
    ");
    $sales = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    return $sales ?: []; // Retorna um array vazio se não houver vendas
}

// Função para obter vendas por categoria
function get_category_sales() {
    global $pdo;
    $stmt = $pdo->query("
        SELECT c.name AS category, SUM(si.quantity * p.price) AS total
        FROM sale_items si
        JOIN products p ON si.product_id = p.id
        JOIN categories c ON p.category_id = c.id
        JOIN sales s ON si.sale_id = s.id
        WHERE s.status = 'completed'
        GROUP BY c.name
    ");
    $categories = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    return $categories ?: []; // Retorna um array vazio se não houver vendas
}
// Função para obter vendas semanais
function get_weekly_sales() {
    global $pdo;
    $stmt = $pdo->query("
        SELECT DATE_FORMAT(sale_date, '%Y-%m-%d') AS date, SUM(total_amount) AS total
        FROM sales
        WHERE sale_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        GROUP BY date
        ORDER BY date
    ");
    $sales = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    return $sales ?: []; // Retorna um array vazio se não houver vendas
}
