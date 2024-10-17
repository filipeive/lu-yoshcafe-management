<?php
require_once '../../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $user_id = filter_var($_POST['user_id'], FILTER_SANITIZE_NUMBER_INT);
    $user = get_user_by_id($user_id);

    if ($user) {
        echo json_encode(['success' => true, 'user' => $user]);
    } else {
        echo json_encode(['success' => false]);
    }
}
