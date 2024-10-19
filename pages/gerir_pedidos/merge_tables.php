<?php
require_once '../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['table_ids'])) {
    $table_ids = $_POST['table_ids'];
    
    try {
        $result = merge_tables($table_ids);
        if ($result) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Falha ao unir as mesas.']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Requisição inválida.']);
}

function split_table($table_id) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();

        // Obter informações da mesa
        $stmt = $pdo->prepare("SELECT group_id FROM tables WHERE id = ?");
        $stmt->execute([$table_id]);
        $table = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$table) {
            throw new Exception("Mesa não encontrada.");
        }

        if (!$table['group_id']) {
            throw new Exception("Esta mesa não faz parte de um grupo.");
        }

        // Atualizar a mesa selecionada
        $stmt = $pdo->prepare("UPDATE tables SET status = 'free', group_id = NULL WHERE id = ?");
        $stmt->execute([$table_id]);

        // Verificar se ainda existem mesas no grupo
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tables WHERE group_id = ?");
        $stmt->execute([$table['group_id']]);
        $remaining_tables = $stmt->fetchColumn();

        // Se não houver mais mesas no grupo, remover o grupo_id das mesas restantes
        if ($remaining_tables <= 1) {
            $stmt = $pdo->prepare("UPDATE tables SET group_id = NULL WHERE group_id = ?");
            $stmt->execute([$table['group_id']]);
        }

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Erro ao separar mesa: " . $e->getMessage());
        throw $e;
    }
}