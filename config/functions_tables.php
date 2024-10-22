<?php
// Constants for valid statuses
const VALID_TABLE_STATUSES = ['free', 'occupied'];

/**
 * Update table information
 * @param int $id Table ID
 * @param int $number Table number
 * @param string $status Table status
 * @return bool
 * @throws InvalidArgumentException
 */
function update_table($id, $number, $status) {
    global $pdo;
    
    // Input validation
    $id = filter_var($id, FILTER_VALIDATE_INT);
    $number = filter_var($number, FILTER_VALIDATE_INT);
    $status = filter_var($status, FILTER_SANITIZE_STRING);
    
    if (!in_array($status, VALID_TABLE_STATUSES)) {
        throw new InvalidArgumentException("Invalid status. Must be 'free' or 'occupied'.");
    }
    
    if ($id === false || $number === false) {
        throw new InvalidArgumentException("Invalid ID or table number.");
    }
    
    $stmt = $pdo->prepare("UPDATE tables SET number = ?, status = ? WHERE id = ?");
    return $stmt->execute([$number, $status, $id]);
}

/**
 * Update table status
 * @param int $table_id
 * @param string $status
 * @return array
 * @throws InvalidArgumentException|Exception
 */

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
    //reload the page
    header("Location: dashboard.php");
    exit;
}

/**
 * Delete a table
 * @param int $id
 * @return bool
 * @throws InvalidArgumentException
 */
function delete_table($id) {
    global $pdo;
    
    $id = filter_var($id, FILTER_VALIDATE_INT);
    if ($id === false) {
        throw new InvalidArgumentException("Invalid table ID.");
    }
    
    $stmt = $pdo->prepare("DELETE FROM tables WHERE id = ?");
    return $stmt->execute([$id]);
}

/**
 * Get all tables with their current status
 * @return array
 */
function get_all_tables() {
    global $pdo;
    
    $query = "
        SELECT 
            t.*,
            CASE
                WHEN o.id IS NOT NULL THEN 'com_pedido'
                WHEN t.status = 'occupied' THEN 'ocupada'
                ELSE 'livre'
            END AS real_status,
            t.group_id
        FROM tables t
        LEFT JOIN orders o ON t.id = o.table_id AND o.status = 'active'
        ORDER BY t.number
    ";
    
    $stmt = $pdo->query($query);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}