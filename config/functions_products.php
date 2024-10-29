<?php

// Função para buscar produtos com filtros e paginação
function get_filtered_products($categoryFilter, $searchTerm, $limit, $offset) {
    global $pdo;
    
    $query = "SELECT p.*, c.name as category_name, 
              COALESCE(m.image_path, 
                       CASE 
                           WHEN c.name = 'Comida' THEN m2.image_path 
                           ELSE NULL 
                       END) as menu_image 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          LEFT JOIN menus m ON p.menu_id = m.id 
          LEFT JOIN menus m2 ON p.name = m2.name AND c.name = 'Comida' 
          WHERE p.is_active = 1";
    
    if ($categoryFilter) {
        $query .= " AND c.id = :category_id";
    }
    if ($searchTerm) {
        $query .= " AND p.name LIKE :search";
    }
    
    $query .= " ORDER BY p.name LIMIT :limit OFFSET :offset";
    
    $stmt = $pdo->prepare($query);
    
    if ($categoryFilter) {
        $stmt->bindValue(':category_id', $categoryFilter, PDO::PARAM_INT);
    }
    if ($searchTerm) {
        $stmt->bindValue(':search', '%' . $searchTerm . '%', PDO::PARAM_STR);
    }
    
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll();
}
// Função para criar produto
function create_product($name, $description, $price, $stock_quantity, $category_id, $image = null) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Sanitização dos dados
        $name = trim(strip_tags($name));
        $description = trim(strip_tags($description));
        $price = floatval(str_replace(',', '.', $price));
        $stock_quantity = intval($stock_quantity);
        $category_id = intval($category_id);
        
        // Verifica se é uma comida
        $stmt = $pdo->prepare("SELECT c.name as category_name FROM categories c WHERE c.id = ?");
        $stmt->execute([$category_id]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $image_path = null;
        $menu_id = null;
        
        // Se for categoria Comida, procura no menu
        if ($category && $category['category_name'] === 'Comida') {
            $stmt = $pdo->prepare("SELECT id, image_path FROM menus WHERE name = ?");
            $stmt->execute([$name]);
            $menu_item = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($menu_item) {
                $image_path = $menu_item['image_path'];
                $menu_id = $menu_item['id'];
            }
        }
        
        // Se tem uma nova imagem e não é do menu
        if ($image && is_array($image) && $image['error'] === UPLOAD_ERR_OK && !$menu_id) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            
            if (!in_array($image['type'], $allowed_types)) {
                throw new Exception('Tipo de arquivo não permitido. Use apenas JPG, PNG ou GIF.');
            }
            
            $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/products/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($image['name'], PATHINFO_EXTENSION);
            $new_filename = uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($image['tmp_name'], $upload_path)) {
                $image_path = $new_filename;
            } else {
                throw new Exception('Erro ao fazer upload da imagem.');
            }
        }
        
        // Insere o produto
        $sql = "INSERT INTO products (name, description, price, stock_quantity, category_id, image_path, menu_id, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            $name,
            $description,
            $price,
            $stock_quantity,
            $category_id,
            $image_path,
            $menu_id
        ]);
        
        if (!$result) {
            throw new Exception('Erro ao inserir produto no banco de dados.');
        }
        
        $pdo->commit();
        return true;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log('Erro ao criar produto: ' . $e->getMessage());
        return false;
    }
}

// Função para atualizar produto
function update_product($id, $name, $description, $price, $stock_quantity, $category_id, $image = null) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Sanitização dos dados
        $id = intval($id);
        $name = trim(strip_tags($name));
        $description = trim(strip_tags($description));
        $price = floatval(str_replace(',', '.', $price));
        $stock_quantity = intval($stock_quantity);
        $category_id = intval($category_id);
        
        // Busca produto atual
        $stmt = $pdo->prepare("SELECT image_path FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $current_product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verifica se é uma comida
        $stmt = $pdo->prepare("SELECT c.name as category_name FROM categories c WHERE c.id = ?");
        $stmt->execute([$category_id]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $image_path = $current_product['image_path'];
        $menu_id = null;
        
        // Se for categoria Comida, procura no menu
        if ($category && $category['category_name'] === 'Comida') {
            $stmt = $pdo->prepare("SELECT id, image_path FROM menus WHERE name = ?");
            $stmt->execute([$name]);
            $menu_item = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($menu_item) {
                $image_path = $menu_item['image_path'];
                $menu_id = $menu_item['id'];
            }
        }
        
        // Se tem uma nova imagem e não é do menu
        if ($image && is_array($image) && $image['error'] === UPLOAD_ERR_OK && !$menu_id) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            
            if (!in_array($image['type'], $allowed_types)) {
                throw new Exception('Tipo de arquivo não permitido. Use apenas JPG, PNG ou GIF.');
            }
            
            $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/products/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Remove imagem antiga se existir
            if ($current_product['image_path'] && file_exists($upload_dir . $current_product['image_path'])) {
                unlink($upload_dir . $current_product['image_path']);
            }
            
            $file_extension = pathinfo($image['name'], PATHINFO_EXTENSION);
            $new_filename = uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($image['tmp_name'], $upload_path)) {
                $image_path = $new_filename;
            } else {
                throw new Exception('Erro ao fazer upload da imagem.');
            }
        }
        
        // Atualiza o produto
        $sql = "UPDATE products 
                SET name = ?, 
                    description = ?, 
                    price = ?, 
                    stock_quantity = ?, 
                    category_id = ?, 
                    image_path = ?,
                    menu_id = ?,
                    updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            $name,
            $description,
            $price,
            $stock_quantity,
            $category_id,
            $image_path,
            $menu_id,
            $id
        ]);
        
        if (!$result) {
            throw new Exception('Erro ao atualizar produto no banco de dados.');
        }
        
        $pdo->commit();
        return true;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log('Erro ao atualizar produto: ' . $e->getMessage());
        return false;
    }
}

// Função auxiliar para verificar e criar o diretório de upload
function ensure_upload_directory($dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
    return is_writable($dir);
}
/*
// Função para criar produto
function create_product($name, $description, $price, $stock_quantity, $category_id, $image = null) {
    global $pdo;
    
    $name = filter_var($name, FILTER_SANITIZE_STRING);
    $description = filter_var($description, FILTER_SANITIZE_STRING);
    $price = filter_var($price, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $stock_quantity = filter_var($stock_quantity, FILTER_SANITIZE_NUMBER_INT);
    $category_id = filter_var($category_id, FILTER_SANITIZE_NUMBER_INT);
    
    // Verifica se é uma comida e existe no menu
    $stmt = $pdo->prepare("SELECT c.name as category_name FROM categories c WHERE c.id = ?");
    $stmt->execute([$category_id]);
    $category = $stmt->fetch();
    
    if ($category['category_name'] === 'Comida') {
        // Verifica se existe no menu
        $stmt = $pdo->prepare("SELECT id, image_path FROM menus WHERE name = ?");
        $stmt->execute([$name]);
        $menu_item = $stmt->fetch();
        
        // Se existir no menu, usa a imagem do menu
        if ($menu_item) {
            $image_path = $menu_item['image_path'];
        }
    }
    
    // Se tiver uma nova imagem
    if ($image && $image['error'] === UPLOAD_ERR_OK) {
        $image_path = handle_image_upload($image, 'products');
    }
    
    if (isset($image_path)) {
        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, stock_quantity, 
                              category_id, image_path, created_at, updated_at) 
                              VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)");
        return $stmt->execute([$name, $description, $price, $stock_quantity, $category_id, $image_path]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, stock_quantity, 
                              category_id, created_at, updated_at) 
                              VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)");
        return $stmt->execute([$name, $description, $price, $stock_quantity, $category_id]);
    }
}
// Função para atualizar produto
function update_product($id, $name, $description, $price, $stock_quantity, $category_id, $image = null) {
    global $pdo;
    
    $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
    $name = filter_var($name, FILTER_SANITIZE_STRING);
    $description = filter_var($description, FILTER_SANITIZE_STRING);
    $price = filter_var($price, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $stock_quantity = filter_var($stock_quantity, FILTER_SANITIZE_NUMBER_INT);
    $category_id = filter_var($category_id, FILTER_SANITIZE_NUMBER_INT);
    
    // Verifica se é uma comida e existe no menu
    $stmt = $pdo->prepare("SELECT c.name as category_name FROM categories c WHERE c.id = ?");
    $stmt->execute([$category_id]);
    $category = $stmt->fetch();
    
    if ($category['category_name'] === 'Comida') {
        // Verifica se existe no menu
        $stmt = $pdo->prepare("SELECT id, image_path FROM menus WHERE name = ?");
        $stmt->execute([$name]);
        $menu_item = $stmt->fetch();
        
        // Se existir no menu, usa a imagem do menu
        if ($menu_item) {
            $image_path = $menu_item['image_path'];
        }
    }
    
    // Se tiver uma nova imagem
    if ($image && $image['error'] === UPLOAD_ERR_OK) {
        $image_path = handle_image_upload($image, 'products');
    }
    
    if (isset($image_path)) {
        $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, 
                              stock_quantity = ?, category_id = ?, image_path = ?, 
                              updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        return $stmt->execute([$name, $description, $price, $stock_quantity, $category_id, $image_path, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, 
                              stock_quantity = ?, category_id = ?, 
                              updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        return $stmt->execute([$name, $description, $price, $stock_quantity, $category_id, $id]);
    }
}
*/
// Função auxiliar para manipular upload de imagens
/*
function handle_image_upload($image, $folder) {
    $target_dir = "uploads/" . $folder . "/";
    
    // Cria o diretório se não existir
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    // Gera um nome único para o arquivo
    $file_extension = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
    $file_name = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $file_name;
    
    // Verifica o tipo de arquivo
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($file_extension, $allowed_types)) {
        throw new Exception('Tipo de arquivo não permitido');
    }
    
    // Move o arquivo para o diretório de destino
    if (move_uploaded_file($image['tmp_name'], $target_file)) {
        return $file_name;
    } else {
        throw new Exception('Erro ao fazer upload da imagem');
    }
}*/

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
//buscar categoria de produto por ID
function get_category_name($category_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT name FROM categories WHERE id =?");
    $stmt->execute([$category_id]);
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


// Função para contar o total de produtos
function count_filtered_products($categoryFilter, $searchTerm) {
    global $pdo;
    $query = "SELECT COUNT(*) FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";

    if ($categoryFilter) {
        $query .= " AND c.id = :category_id";
    }

    if ($searchTerm) {
        $query .= " AND p.name LIKE :search";
    }

    $stmt = $pdo->prepare($query);
    
    if ($categoryFilter) {
        $stmt->bindValue(':category_id', $categoryFilter, PDO::PARAM_INT);
    }

    if ($searchTerm) {
        $stmt->bindValue(':search', '%' . $searchTerm . '%', PDO::PARAM_STR);
    }

    $stmt->execute();
    return $stmt->fetchColumn();
}
function get_products_image() {
    global $pdo; // Supondo que você tenha uma conexão PDO estabelecida
    $stmt = $pdo->query("SELECT id, name, image_path FROM products");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
