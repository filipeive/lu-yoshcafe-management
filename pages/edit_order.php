<?php
require_once '../config/config.php';
require_login();

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$order = order_get_by_id($order_id); // Verificar se o pedido existe

if (!$order) {
    $_SESSION['error_message'] = "Pedido não encontrado.";
    header("Location: orders.php");
    exit;
}

$table_id = $order['table_id']; // Obter o ID da mesa a partir do pedido
$tables = get_all_tables();
$order_items = order_get_items($order_id); // Obtendo os itens do pedido
$available_products = get_available_products();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    
    if ($product_id > 0 && $quantity > 0) {
        add_item_to_order($order_id, $product_id, $quantity);
        $_SESSION['success_message'] = "Item adicionado ao pedido.";
        header("Location: edit_order.php?id=" . $order_id);
        exit;
    }
}

// Verificar se a mesa está associada ao pedido
if (!$table_id) {
    die("Mesa não especificada.");
}

// Busque o pedido associado à mesa, se necessário
$stmt = $pdo->prepare("SELECT * FROM orders WHERE table_id = ?");
$stmt->execute([$table_id]);
$order = $stmt->fetch();

if (!$order) {
    die("Pedido não encontrado.");
}

include '../includes/header.php';
?>

<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Editar Pedido #<?php echo $order['id']; ?> - Mesa <?php echo $order['table_number']; ?></h4>
                
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success" role="alert">
                        <?php 
                        echo $_SESSION['success_message']; 
                        unset($_SESSION['success_message']);
                        ?>
                    </div>
                <?php endif; ?>

                <h5>Itens do Pedido</h5>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Quantidade</th>
                            <th>Preço Unitário</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_items as $item): ?>
                        <tr>
                            <td><?php echo $item['product_name']; ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td>MZN <?php echo number_format($item['product_price'], 2, ',', '.'); ?></td>
                            <td>MZN <?php echo number_format($item['quantity'] * $item['product_price'], 2, ',', '.'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3">Total:</th>
                            <th>MZN <?php echo number_format($order['total_amount'], 2, ',', '.'); ?></th>
                        </tr>
                    </tfoot>
                </table>

                <h5 class="mt-4">Adicionar Item</h5>
                <form method="post">
                    <div class="form-group">
                        <label for="product_id">Produto</label>
                        <select name="product_id" id="product_id" class="form-control" required>
                            <option value="">Selecione um produto</option>
                            <?php foreach ($available_products as $product): ?>
                                <option value="<?php echo $product['id']; ?>"><?php echo $product['name']; ?> - MZN <?php echo number_format($product['price'], 2, ',', '.'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="quantity">Quantidade</label>
                        <input type="number" name="quantity" id="quantity" class="form-control" min="1" value="1" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Adicionar Item</button>
                </form>

                <a href="gerir_pedidos/complete_order.php?id=<?php echo $order_id; ?>" class="btn btn-success mt-3" onclick="return confirm('Tem certeza que deseja finalizar este pedido e gerar uma venda?')">Finalizar Pedido</a>
                <a href="orders.php" class="btn btn-secondary mt-3">Voltar para Lista de Pedidos</a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
