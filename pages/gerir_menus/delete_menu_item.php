<?php
require_once '../../config/config.php';
//require_once '../functions/menu_functions.php';
require_login();
require_admin();

if (isset($_GET['id'])) {
    $item = get_menu_item_by_id($_GET['id']);
    echo json_encode($item);
} else {
    echo json_encode(['error' => 'No ID provided']);
}