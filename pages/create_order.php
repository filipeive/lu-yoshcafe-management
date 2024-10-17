<?php
// Arquivo: create_order.php
require_once '../config/config.php';
require_login();

$table_id = isset($_GET['table_id']) ? intval($_GET['table_id']) : 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $table_id = $_POST['table_id'];
    $products = $_POST['products'];
    $quantities = $_POST['quantities'];
    
    // Criar o pedido
    $order_id = order_create($table_id);

    // Adicionar itens ao pedido
    for ($i = 0; $i < count($products); $i++) {
        if ($quantities[$i] > 0) {
            order_add_item($order_id, $products[$i], $quantities[$i]);
        }
    }

    // Atualizar o status da mesa
    update_table_status($table_id, 'free');
    if ($current_status === 'occupied') {
        // Não faça nada ou mostre uma mensagem ao usuário
        echo "A mesa já está ocupada.";
    } else {
        update_table_status($table_id, 'occupied');
    }
    // Redirecionar para visualizar o pedido
    header("Location: view_order.php?id=$order_id");
    exit;
}

// Obter produtos disponíveis
$products = product_get_available();
include '../includes/header.php';
?>

<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Criar Novo Pedido</h4>
                <form method="post">
                    <input type="hidden" name="table_id" value="<?php echo $table_id; ?>">
                    <div class="form-group">
                        <label>Mesa: <?php echo $table_id; ?></label>
                    </div>
                    <div class="form-group">
                        <label>Produtos:</label>
                        <?php foreach ($products as $product): ?>
                        <div class="row mb-2">
                            <div class="col-md-6">
                                <label>
                                    <input type="checkbox" name="products[]" value="<?php echo $product['id']; ?>">
                                    <?php echo $product['name']; ?> - R$
                                    <?php echo number_format($product['price'], 2, ',', '.'); ?>
                                </label>
                            </div>
                            <div class="col-md-6">
                                <input type="number" name="quantities[]" class="form-control" min="0" value="0">
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit" class="btn btn-primary">Criar Pedido</button>
                    <a href="tables.php" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>