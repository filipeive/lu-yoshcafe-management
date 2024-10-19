<?php
require_once '../config/config.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Constants
if (!defined('VALID_TABLE_STATUSES')) {
    define('VALID_TABLE_STATUSES', ['free', 'occupied']);
}

/**
 * Merge multiple tables into a group
 * @param array $table_ids Array of table IDs to merge
 * @return array
 */
function merge_tables($table_ids) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Validate table IDs
        $table_ids = array_map(function($id) {
            return filter_var($id, FILTER_VALIDATE_INT);
        }, $table_ids);
        
        if (in_array(false, $table_ids, true)) {
            throw new InvalidArgumentException("Invalid table ID provided");
        }
        
        // Check if tables are available
        $placeholders = str_repeat('?,', count($table_ids) - 1) . '?';
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tables WHERE id IN ($placeholders) AND status != 'free'");
        $stmt->execute($table_ids);
        
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("Some selected tables are not available");
        }

        // Generate unique group ID
        $group_id = substr(uniqid('g'), 0, 23);
        
        // Calculate total capacity
        $stmt = $pdo->prepare("SELECT SUM(capacity) as total_capacity FROM tables WHERE id IN ($placeholders)");
        $stmt->execute($table_ids);
        $total_capacity = $stmt->fetch()['total_capacity'];
        
        // Update all tables in the group
        $sql = "UPDATE tables SET 
                group_id = ?, 
                merged_capacity = ?,
                status = 'occupied'
                WHERE id IN ($placeholders)";
        
        $params = array_merge([$group_id, $total_capacity], $table_ids);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        $pdo->commit();
        return ['success' => true, 'message' => 'Tables merged successfully'];
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Split a table from its group
 * @param int $table_id ID of the table to split
 * @return array
 */
function split_table($table_id) {
    global $pdo;
    try {
        $table_id = filter_var($table_id, FILTER_VALIDATE_INT);
        if ($table_id === false) {
            throw new InvalidArgumentException("Invalid table ID");
        }

        // Get table information before starting the transaction
        $stmt = $pdo->prepare("SELECT id, group_id, status FROM tables WHERE id = ?");
        $stmt->execute([$table_id]);
        $table = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$table) {
            throw new Exception("Table not found (ID: $table_id)");
        }

        if (!$table['group_id']) {
            throw new Exception("Table is not in a group (ID: $table_id)");
        }

        $pdo->beginTransaction();

        // Reset all tables in the group
        $stmt = $pdo->prepare("UPDATE tables SET
            group_id = NULL,
            is_main = 0,
            merged_capacity = NULL,
            status = 'free'
            WHERE group_id = ?");
        $stmt->execute([$table['group_id']]);

        $rowsUpdated = $stmt->rowCount();
        if ($rowsUpdated === 0) {
            throw new Exception("No tables were updated");
        }

        $pdo->commit();
        return ['success' => true, 'message' => "$rowsUpdated tables have been split"];
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Error splitting table: " . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['action'])) {
        echo json_encode(['success' => false, 'message' => 'Action not specified']);
        exit;
    }

    $response = ['success' => false, 'message' => 'Invalid action'];
    
    switch ($_POST['action']) {
        case 'merge':
            if (!isset($_POST['table_ids']) || !is_array($_POST['table_ids'])) {
                $response = ['success' => false, 'message' => 'Table IDs not provided'];
                break;
            }
            $response = merge_tables($_POST['table_ids']);
            break;
            
        case 'split':
            if (!isset($_POST['table_id'])) {
                $response = ['success' => false, 'message' => 'Table ID not provided'];
                break;
            }
            $response = split_table($_POST['table_id']);
            break;
            
        case 'update':
            if (!isset($_POST['id']) || !isset($_POST['number']) || !isset($_POST['status'])) {
                $response = ['success' => false, 'message' => 'Missing required parameters'];
                break;
            }
            $response = update_table($_POST['id'], $_POST['number'], $_POST['status']);
            break;
            
        case 'get_all':
            $response = get_all_tables();
            break;
    }
    
    echo json_encode($response);
    exit;
}


/*
 * Update table status
 * @param int $table_id
 * @param string $status
 * @return array
 * @throws InvalidArgumentException|Exception
 *

 function update_table_status($table_id, $status) {
    global $pdo;
    
    // Input validation
    $table_id = filter_var($table_id, FILTER_VALIDATE_INT);
    if ($table_id === false) {
        throw new InvalidArgumentException("Invalid table ID.");
    }
    
    if (!in_array($status, VALID_TABLE_STATUSES)) {
        throw new InvalidArgumentException("Invalid status. Must be 'free' or 'occupied'.");
    }
    
    // Check current table status
    $stmt = $pdo->prepare("SELECT status FROM tables WHERE id = ?");
    $stmt->execute([$table_id]);
    $current_status = $stmt->fetchColumn();
    
    if ($current_status === false) {
        throw new Exception("No table found with ID $table_id.");
    }
    
    // If status hasn't changed, return early
    if ($current_status === $status) {
        return [
            'success' => true, 
            'message' => "The status of table ID $table_id is already '$status'."
        ];
    }
    
    // Update the status
    $stmt = $pdo->prepare("UPDATE tables SET status = ? WHERE id = ?");
    $stmt->execute([$status, $table_id]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception("Failed to update status for table ID $table_id.");
    }
    
    return [
        'success' => true,
        'message' => "Table ID $table_id status updated to '$status'."
    ];
}
*/