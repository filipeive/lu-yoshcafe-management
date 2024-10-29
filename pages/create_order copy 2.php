<?php
// Arquivo: create_order.php
require_once '../config/config.php';
require_login();

$table_id = isset($_GET['table_id']) ? intval($_GET['table_id']) : 0;

// Inicializar a variável $table_ids como um array
$table_ids = [];

// Consultar a mesa principal
if ($table_id > 0) {
    // Verificar se a mesa está em um grupo
    $stmt = $pdo->prepare("SELECT group_id FROM tables WHERE id = ?");
    $stmt->execute([$table_id]);
    $group = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($group && $group['group_id']) {
        // Mesa faz parte de um grupo, pegar todas as mesas unidas
        $stmt = $pdo->prepare("SELECT id FROM tables WHERE group_id = ?");
        $stmt->execute([$group['group_id']]);
        $table_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } else {
        // Mesa única
        $table_ids = [$table_id];
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $products = $_POST['products'];
    $quantities = $_POST['quantities'];

    // Criar o pedido
    $order_id = order_create($table_ids);

    // Adicionar itens ao pedido para cada mesa unida
    foreach ($table_ids as $table_id) {
        for ($i = 0; $i < count($products); $i++) {
            if ($quantities[$i] > 0) {
                order_add_item($order_id, $products[$i], $quantities[$i]);
            }
        }
    }

    // Atualizar o status das mesas
    foreach ($table_ids as $table_id) {
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

<!-- HTML para criar pedidos -->
<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Criar Novo Pedido</h4>
                <form method="post">
                    <input type="hidden" name="table_id" value="<?php echo $table_id; ?>">
                    <div class="form-group">
                        <label>Mesa: <?php echo implode(', ', $table_ids); ?></label>
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

