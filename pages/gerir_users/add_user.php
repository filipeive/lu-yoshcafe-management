<?php
require_once '../../config/config.php';
require_login();

$data = json_decode(file_get_contents("php://input"));

if (create_user($data->username, $data->password, $data->name, $data->role)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>
