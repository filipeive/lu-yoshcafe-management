<?php
require_once '../config/config.php';
require_login();

$table_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($table_id > 0) {
    $stmt = $pdo->prepare("UPDATE tables SET status = 'occupied' WHERE id = ? AND status = 'free'");
    if ($stmt->execute([$table_id])) {
        $_SESSION['success_message'] = "Mesa ocupada com sucesso.";
    } else {
        $_SESSION['error_message'] = "Erro ao ocupar a mesa.";
    }
}

header("Location: tables.php");
exit;