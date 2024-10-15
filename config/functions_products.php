<?php
// Funções de Produtos
function create_product($name, $description, $price, $stock_quantity, $category_id) {
    global $pdo;
    
    // Sanitização dos inputs
    $name = filter_var($name, FILTER_SANITIZE_STRING);
    $description = filter_var($description, FILTER_SANITIZE_STRING);
    $price = filter_var($price, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $stock_quantity = filter_var($stock_quantity, FILTER_SANITIZE_NUMBER_INT);
    $category_id = filter_var($category_id, FILTER_SANITIZE_NUMBER_INT);
    
    // Prepara a query de inserção
    $stmt = $pdo->prepare("INSERT INTO products (name, description, price, stock_quantity, category_id) VALUES (?, ?, ?, ?, ?)");
    
    // Executa a query com os parâmetros
    return $stmt->execute([$name, $description, $price, $stock_quantity, $category_id]);
}

function update_product($id, $name, $description, $price, $stock_quantity, $category_id) {
    global $pdo;
    
    $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
    $name = filter_var($name, FILTER_SANITIZE_STRING);
    $description = filter_var($description, FILTER_SANITIZE_STRING);
    $price = filter_var($price, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $stock_quantity = filter_var($stock_quantity, FILTER_SANITIZE_NUMBER_INT);
    $category_id = filter_var($category_id, FILTER_SANITIZE_NUMBER_INT);
    
    $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock_quantity = ?, category_id = ? WHERE id = ?");
    return $stmt->execute([$name, $description, $price, $stock_quantity, $category_id, $id]);
}

function get_all_products() {
    global $pdo;
    $stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.name");
    return $stmt->fetchAll();
}

function get_product_by_id($id) {
    global $pdo;

    // Sanitiza o ID para evitar injeção de SQL
    $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);

    // Prepara a consulta para buscar o produto pelo ID
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
    $stmt->execute([$id]);

    // Retorna o produto se encontrado
    return $stmt->fetch();
}


// Funções de Categorias
function get_all_categories() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
    return $stmt->fetchAll();
}

function create_category($name) {
    global $pdo;
    $name = filter_var($name, FILTER_SANITIZE_STRING);
    $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
    return $stmt->execute([$name]);
}

// Função para atualizar o estoque
function update_stock($product_id, $quantity) {
    global $pdo;
    $product_id = filter_var($product_id, FILTER_SANITIZE_NUMBER_INT);
    $quantity = filter_var($quantity, FILTER_SANITIZE_NUMBER_INT);
    $stmt = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity + ? WHERE id = ?");
    return $stmt->execute([$quantity, $product_id]);
}
function get_low_stock_products() {
    global $pdo;
    $stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE stock_quantity < 10");
    return $stmt->fetchColumn();
}
