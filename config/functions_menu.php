<?php

//functions menu
function create_menu_item($name, $description, $price, $category, $image_path, $is_active = 1) {
    global $pdo;
    
    $name = filter_var($name, FILTER_SANITIZE_STRING);
    $description = filter_var($description, FILTER_SANITIZE_STRING);
    $price = filter_var($price, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $category = filter_var($category, FILTER_SANITIZE_STRING);
    $image_path = filter_var($image_path, FILTER_SANITIZE_URL);
    $is_active = filter_var($is_active, FILTER_VALIDATE_BOOLEAN);
    
    $stmt = $pdo->prepare("INSERT INTO menus (name, description, price, category, image_path, is_active) VALUES (?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$name, $description, $price, $category, $image_path, $is_active]);
}

function get_menu_item_by_id($id) {
    global $pdo;
    $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
    $stmt = $pdo->prepare("SELECT * FROM menus WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function update_menu_item($id, $name, $description, $price, $category, $image_path, $is_active) {
    global $pdo;
    
    $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
    $name = filter_var($name, FILTER_SANITIZE_STRING);
    $description = filter_var($description, FILTER_SANITIZE_STRING);
    $price = filter_var($price, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $category = filter_var($category, FILTER_SANITIZE_STRING);
    $image_path = filter_var($image_path, FILTER_SANITIZE_URL);
    $is_active = filter_var($is_active, FILTER_VALIDATE_BOOLEAN);
    
    $stmt = $pdo->prepare("UPDATE menus SET name = ?, description = ?, price = ?, category = ?, image_path = ?, is_active = ? WHERE id = ?");
    return $stmt->execute([$name, $description, $price, $category, $image_path, $is_active, $id]);
}

function delete_menu_item($id) {
    global $pdo;
    $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
    $stmt = $pdo->prepare("DELETE FROM menus WHERE id = ?");
    return $stmt->execute([$id]);
}

function get_all_menu_items() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM menus ORDER BY category, name");
    return $stmt->fetchAll();
}

function get_active_menu_items() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM menus WHERE is_active = 1 ORDER BY category, name");
    return $stmt->fetchAll();
}

function get_menu_items_by_category($category) {
    global $pdo;
    $category = filter_var($category, FILTER_SANITIZE_STRING);
    $stmt = $pdo->prepare("SELECT * FROM menus WHERE category = ? AND is_active = 1 ORDER BY name");
    $stmt->execute([$category]);
    return $stmt->fetchAll();
}

function get_menu_categories() {
    global $pdo;
    $stmt = $pdo->query("SELECT DISTINCT category FROM menus WHERE is_active = 1 ORDER BY category");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function handle_image_upload($file) {
    $target_dir = "../uploads/menu/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $unique_file_name = uniqid() . '.' . $imageFileType;
    $target_file = $target_dir . $unique_file_name;

    // Verifique se é uma imagem real
    $check = getimagesize($file["tmp_name"]);
    if($check === false) {
        throw new Exception("O arquivo não é uma imagem.");
    }

    // Verifique o tamanho do arquivo
    if ($file["size"] > 5000000) {
        throw new Exception("Desculpe, seu arquivo é muito grande.");
    }

    // Permita apenas certos formatos de arquivo
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
        throw new Exception("Desculpe, apenas arquivos JPG, JPEG, PNG e GIF são permitidos.");
    }

    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $target_file;
    } else {
        throw new Exception("Desculpe, houve um erro ao fazer o upload do seu arquivo.");
    }
}