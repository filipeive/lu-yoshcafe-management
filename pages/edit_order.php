<?php
require_once '../config/config.php';
require_login();

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$order = order_get_by_id($order_id);

if (!$order) {
    $_SESSION['error_message'] = "Pedido não encontrado.";
    header("Location: orders.php");
    exit;
}

$table_id = $order['table_id'];
$tables = get_all_tables();
$order_items = order_get_items($order_id);
$available_products = get_available_products();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $product_id = intval($_POST['product_id']);
            $quantity = intval($_POST['quantity']);
            
            if ($product_id > 0 && $quantity > 0) {
                add_item_to_order($order_id, $product_id, $quantity);
                update_order_total($order_id); // Atualiza o total após adicionar
                $_SESSION['success_message'] = "Item adicionado ao pedido.";
            } else {
                $_SESSION['error_message'] = "Erro ao adicionar item. Verifique os dados.";
            }
        } elseif ($_POST['action'] === 'remove') {
            $item_id = intval($_POST['item_id']);
            if ($item_id > 0) {
                remove_item_from_order($order_id, $item_id);
                update_order_total($order_id); // Atualiza o total após remover
                $_SESSION['success_message'] = "Item removido do pedido.";
            } else {
                $_SESSION['error_message'] = "Erro ao remover item. ID inválido.";
            }
        }
        header("Location: edit_order.php?id=" . $order_id);
        exit;
    }
}

include '../includes/header.php';
?>


<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title">
                        <i class="mdi mdi-clipboard-text me-2"></i>
                        Pedido #<?php echo $order['id']; ?> - Mesa <?php echo $order['table_number']; ?>
                    </h4>
                    <div class="order-status">
                        <span class="badge bg-primary">Em Andamento</span>
                    </div>
                </div>

                <!-- Mensagens de sucesso e erro -->
                <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="mdi mdi-check-circle-outline me-2"></i>
                    <?php 
                                echo $_SESSION['success_message']; 
                                unset($_SESSION['success_message']);
                                ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="mdi mdi-alert-circle-outline me-2"></i>
                    <?php 
                                echo $_SESSION['error_message']; 
                                unset($_SESSION['error_message']);
                                ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <div class="row">
                    <!-- Coluna dos Itens do Pedido -->
                    <div class="col-md-8">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title mb-4">
                                    <i class="mdi mdi-food-fork-drink me-2"></i>
                                    Itens do Pedido
                                </h5>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Produto</th>
                                                <th>Quantidade</th>
                                                <th>Preço Unitário</th>
                                                <th>Subtotal</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($order_items as $item): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="mdi mdi-food me-2 text-primary"></i>
                                                        <?php echo $item['product_name']; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info"><?php echo $item['quantity']; ?></span>
                                                </td>
                                                <td>MZN
                                                    <?php echo number_format($item['product_price'], 2, ',', '.'); ?>
                                                </td>
                                                <td>
                                                    <strong>MZN
                                                        <?php echo number_format($item['quantity'] * $item['product_price'], 2, ',', '.'); ?></strong>
                                                </td>
                                                <td>
                                                    <form method="post" class="d-inline">
                                                        <input type="hidden" name="action" value="remove">
                                                        <input type="hidden" name="item_id"
                                                            value="<?php echo $item['id']; ?>">
                                                        <button type="submit" class="btn btn-outline-danger btn-sm"
                                                            onclick="return confirm('Tem certeza que deseja remover este item?')">
                                                            <i class="mdi mdi-delete"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                                <td colspan="2"><strong class="text-primary">MZN
                                                        <?php echo number_format($order['total_amount'], 2, ',', '.'); ?></strong>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Coluna do Formulário de Adição -->
                    <div class="col-md-4">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title mb-4">
                                    <i class="mdi mdi-plus-circle me-2"></i>
                                    Adicionar Item
                                </h5>
                                <form method="post" class="forms-sample">
                                    <input type="hidden" name="action" value="add">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="product_id">Produto</label>
                                        <select name="product_id" id="product_id" class="form-select" required>
                                            <option value="">Selecione um produto</option>
                                            <?php foreach ($available_products as $product): ?>
                                            <option value="<?php echo $product['id']; ?>">
                                                <?php echo $product['name']; ?> - MZN
                                                <?php echo number_format($product['price'], 2, ',', '.'); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group mb-4">
                                        <label class="form-label" for="quantity">Quantidade</label>
                                        <div class="input-group">
                                            <button type="button" class="btn btn-outline-secondary"
                                                onclick="decrementQuantity()">-</button>
                                            <input type="number" name="quantity" id="quantity"
                                                class="form-control text-center" min="1" value="1" required>
                                            <button type="button" class="btn btn-outline-secondary"
                                                onclick="incrementQuantity()">+</button>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="mdi mdi-plus-circle-outline me-2"></i>
                                        Adicionar Item
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Ações do Pedido -->
                        <div class="card shadow-sm mt-4">
                            <div class="card-body">
                                <h5 class="card-title mb-4">
                                    <i class="mdi mdi-cog me-2"></i>
                                    Ações do Pedido
                                </h5>
                                <div class="d-grid gap-2">
                                    <a href="gerir_pedidos/complete_order.php?id=<?php echo $order_id; ?>"
                                        class="btn btn-success"
                                        onclick="return confirm('Tem certeza que deseja finalizar este pedido e gerar uma venda?')">
                                        <i class="mdi mdi-check-circle me-2"></i>
                                        Finalizar Pedido
                                    </a>
                                    <a href="orders.php" class="btn btn-outline-secondary">
                                        <i class="mdi mdi-arrow-left me-2"></i>
                                        Voltar para Lista de Pedidos
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>

<script>
function incrementQuantity() {
    const input = document.getElementById('quantity');
    input.value = parseInt(input.value) + 1;
}

function decrementQuantity() {
    const input = document.getElementById('quantity');
    const currentValue = parseInt(input.value);
    if (currentValue > 1) {
        input.value = currentValue - 1;
    }
}

// Fechar alerts automaticamente após 5 segundos
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
});
</script>

<?php include '../includes/footer.php'; ?>