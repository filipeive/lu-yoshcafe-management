<?php
// add_product.php
require_once '../../config/config.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validação básica dos campos
        $name = filter_var($_POST['name'] ?? '', FILTER_SANITIZE_STRING);
        $description = filter_var($_POST['description'] ?? '', FILTER_SANITIZE_STRING);
        $price = filter_var($_POST['price'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $stock_quantity = filter_var($_POST['stock_quantity'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
        $category_id = filter_var($_POST['category_id'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
        
        // Verifica se tem imagem
        $image = null;
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['product_image']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('Erro no upload da imagem: ' . $_FILES['product_image']['error']);
            }
            $image = $_FILES['product_image'];
        }
        
        // Tenta criar o produto
        if (create_product($name, $description, $price, $stock_quantity, $category_id, $image)) {
            echo json_encode([
                'success' => true,
                'message' => 'Produto criado com sucesso!'
            ]);
        } else {
            throw new Exception('Erro ao criar produto');
        }
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Erro: ' . $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método não permitido'
    ]);
}
