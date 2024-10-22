<?php
require_once '../config/config.php'; // Inclua sua conexão com o banco de dados

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Função para lidar com erros
function handleError($message) {
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

// Função para lidar com sucessos
function handleSuccess($message) {
    echo json_encode(['success' => true, 'message' => $message]);
    exit;
}

// Verifica se a ação foi definida
if (!isset($_POST['action'])) {
    handleError('Ação não definida.');
}

$action = $_POST['action'];

// Lidar com as diferentes ações
switch ($action) {
    case 'occupy':
        if (!isset($_POST['table_id'])) {
            handleError('ID da mesa não definido.');
        }

        $table_id = (int) $_POST['table_id'];  // Certificar-se de que o ID seja um inteiro

        // Verificar se a mesa já está ocupada
        $sql = "SELECT status FROM tables WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$table_id]);
        $current_status = $stmt->fetchColumn();

        if ($current_status === 'occupied') {
            handleError('A mesa já está ocupada.');
        }

        // Atualiza o status da mesa para 'ocupada'
        $sql = "UPDATE tables SET status = 'occupied' WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$table_id])) {
            handleSuccess('Mesa ocupada com sucesso.');
        } else {
            handleError('Erro ao ocupar a mesa.');
        }
        break;

    case 'free':
        if (!isset($_POST['table_id'])) {
            handleError('ID da mesa não definido.');
        }

        $table_id = (int) $_POST['table_id'];  // Certificar-se de que o ID seja um inteiro

        // Verificar se a mesa já está livre
        $sql = "SELECT status FROM tables WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$table_id]);
        $current_status = $stmt->fetchColumn();

        if ($current_status === 'free') {
            handleError('A mesa já está livre.');
        }

        // Atualiza o status da mesa para 'livre'
        $sql = "UPDATE tables SET status = 'free' WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$table_id])) {
            handleSuccess('Mesa liberada com sucesso.');
        } else {
            handleError('Erro ao liberar a mesa.');
        }
        break;

    case 'merge':
        if (!isset($_POST['table_ids']) || !is_array($_POST['table_ids'])) {
            handleError('IDs das mesas não definidos ou formato inválido.');
        }

        $table_ids = array_map('intval', $_POST['table_ids']);  // Certificar-se de que todos os IDs sejam inteiros

        // Adicionar lógica para unir mesas
        $response = merge_tables($table_ids);  // Função já implementada no seu código
        echo json_encode($response);
        break;

    case 'split':
        if (!isset($_POST['table_id'])) {
            handleError('ID da mesa não definido.');
        }

        $table_id = (int) $_POST['table_id'];  // Certificar-se de que o ID seja um inteiro

        // Adicionar lógica para separar a mesa
        $response = split_table($table_id);  // Função já implementada no seu código
        echo json_encode($response);
        break;

    default:
        handleError('Ação inválida.');
}

// Função para unir mesas
function merge_tables($table_ids) {
    global $pdo;
    try {
        $pdo->beginTransaction();
        
        // Validar IDs das mesas
        $table_ids = array_map('intval', $table_ids);
        $placeholders = implode(',', array_fill(0, count($table_ids), '?'));

        // Verificar se todas as mesas estão livres
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tables WHERE id IN ($placeholders) AND status != 'free'");
        $stmt->execute($table_ids);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("Algumas mesas selecionadas não estão disponíveis.");
        }

        // Criar um ID de grupo
        $group_id = substr(uniqid('g'), 0, 23);

        // Atualizar mesas para o grupo
        $stmt = $pdo->prepare("UPDATE tables SET group_id = ?, status = 'occupied' WHERE id IN ($placeholders)");
        $stmt->execute(array_merge([$group_id], $table_ids));

        $pdo->commit();
        return ['success' => true, 'message' => 'Mesas unidas com sucesso.'];
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// Função para separar uma mesa
function split_table($table_id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT group_id FROM tables WHERE id = ?");
        $stmt->execute([$table_id]);
        $group = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$group['group_id']) {
            throw new Exception("A mesa não está em um grupo.");
        }

        $pdo->beginTransaction();

        // Separar as mesas do grupo
        $stmt = $pdo->prepare("UPDATE tables SET group_id = NULL, status = 'free' WHERE group_id = ?");
        $stmt->execute([$group['group_id']]);

        $pdo->commit();
        return ['success' => true, 'message' => 'Mesas separadas com sucesso.'];
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}
