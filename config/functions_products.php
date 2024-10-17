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
// Adicione esta função se ainda não existir
function get_product_name($product_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT name FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    return $stmt->fetchColumn();
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
function get_available_products() {
    global $pdo; // Supondo que você tenha uma conexão PDO estabelecida
    $stmt = $pdo->query("SELECT id, name, price FROM products WHERE stock_quantity > 0");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function add_item_to_order($order_id, $product_id, $quantity) {
    global $pdo;

    // Verifique se o pedido existe
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();

    if (!$order) {
        throw new Exception("Pedido não encontrado.");
    }

    // Verifique se o produto existe
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        throw new Exception("Produto não encontrado.");
    }

    // Adicione o item ao pedido sem incluir o preço na inserção
    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity) VALUES (?, ?, ?)");
    $stmt->execute([$order_id, $product_id, $quantity]);

    // Opcional: Atualize o total do pedido, se necessário
    $total_amount = $order['total_amount'] + ($product['price'] * $quantity); // Você pode precisar de uma coluna 'total_amount' na tabela 'orders'.
    $stmt = $pdo->prepare("UPDATE orders SET total_amount = ? WHERE id = ?");
    $stmt->execute([$total_amount, $order_id]);
}

