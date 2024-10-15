
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
