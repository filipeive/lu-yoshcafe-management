<?php
require_once '../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['table_id'])) {
    $table_id = $_POST['table_id'];
    
    try {
        split_table($table_id);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Requisição inválida.']);
}