<?php
require_once '../../config/config.php';
require_login();

$id = $_GET['id'];

if (delete_user($id)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>
