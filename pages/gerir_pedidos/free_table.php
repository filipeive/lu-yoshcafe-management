<?php
require_once '../config/config.php';
require_login();

$table_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($table_id > 0) {
    $stmt = $pdo->prepare("UPDATE tables SET status = 'free' WHERE id = ? AND status = 'occupied'");
    if ($stmt->execute([$table_id])) {
        $_SESSION['success_message'] = "Mesa liberada com sucesso.";
    } else {
        $_SESSION['error_message'] = "Erro ao liberar a mesa.";
    }
}

header("Location: tables.php");
exit;