<?php
require_once '../../config/config.php';
require_login();

$table_id = isset($_GET['table_id']) ? intval($_GET['table_id']) : 0;

if ($table_id > 0) {
    $table = get_table_by_id($table_id);
    
    if ($table && $table['status'] == 'occupied') {
        $order_id = create_order($table_id);
        
        if ($order_id) {
            $_SESSION['success_message'] = "Novo pedido criado para a mesa " . $table['number'];
            header("Location: edit_order.php?id=" . $order_id);
            exit;
        } else {
            $_SESSION['error_message'] = "Erro ao criar o pedido.";
        }
    } else {
        $_SESSION['error_message'] = "Mesa inválida ou não está ocupada.";
    }
}

header("Location: tables.php");
exit;