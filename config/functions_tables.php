<?php

function update_table($id, $number, $status) {
    global $pdo;
    
    $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
    $number = filter_var($number, FILTER_SANITIZE_NUMBER_INT);
    $status = filter_var($status, FILTER_SANITIZE_STRING);
    
    $stmt = $pdo->prepare("UPDATE tables SET number = ?, status = ? WHERE id = ?");
    return $stmt->execute([$number, $status, $id]);
}

function delete_table($id) {
    global $pdo;
    $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
    $stmt = $pdo->prepare("DELETE FROM tables WHERE id = ?");
    return $stmt->execute([$id]);
}
function get_all_tables() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM tables ORDER BY number");
    return $stmt->fetchAll();
}  