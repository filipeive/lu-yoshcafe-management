<?php
require_once '../../config/config.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validação básica dos campos
        $id = filter_var($_POST['id'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
        $name = filter_var($_POST['name'] ?? '', FILTER_SANITIZE_STRING);
        $description = filter_var($_POST['description'] ?? '', FILTER_SANITIZE_STRING);
        $price = filter_var($_POST['price'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $stock_quantity = filter_var($_POST['stock_quantity'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
        $category_id = filter_var($_POST['category_id'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
        
        // Verifica se tem nova imagem
        $image = null;
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['product_image']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('Erro no upload da imagem: ' . $_FILES['product_image']['error']);
            }
            $image = $_FILES['product_image'];
        }
        
        // Tenta atualizar o produto
        if (update_product($id, $name, $description, $price, $stock_quantity, $category_id, $image)) {
            // Busca o produto atualizado para retornar dados atualizados
            $stmt = $pdo->prepare("SELECT p.*, c.name as category_name 
                                 FROM products p 
                                 LEFT JOIN categories c ON p.category_id = c.id 
                                 WHERE p.id = ?");
            $stmt->execute([$id]);
            $updated_product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'message' => 'Produto atualizado com sucesso!',
                'product' => $updated_product
            ]);
        } else {
            throw new Exception('Erro ao atualizar produto');
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
