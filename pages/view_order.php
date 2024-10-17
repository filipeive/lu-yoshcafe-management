<?php
// Arquivo: view_order.php
require_once '../config/config.php';
require_login();

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$order = order_get_by_id($order_id); // Obter detalhes do pedido
$order_items = order_get_items($order_id); // Obter itens do pedido

if (!$order) {
    // Redirecionar para a página de pedidos se o pedido não for encontrado
    header('Location: orders.php');
    exit;
}

include '../includes/header.php';
?>

<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Detalhes do Pedido #<?php echo $order['id']; ?></h4>
                <p><strong>Mesa:</strong> <?php echo $order['table_number']; ?></p>
                <p><strong>Status:</strong> <?php echo $order['status'] == 'completed' ? 'Concluído' : 'Ativo'; ?></p>
                <p><strong>Data:</strong> <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
                
                <h5 class="mt-4">Itens do Pedido</h5>
                <div class="table-responsive">
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
                                <td>MZN<?php echo number_format($item['product_price'], 2, ',', '.'); ?></td>
                                <td>MZN<?php echo number_format($item['quantity'] * $item['product_price'], 2, ',', '.'); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-right">Total:</th>
                                <th>MZN<?php echo number_format($order['total_amount'], 2, ',', '.'); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <?php if ($order['status'] != 'completed'): ?>
                <a href="edit_order.php?id=<?php echo $order['id']; ?>" class="btn btn-warning mt-3">Editar Pedido</a>
                <?php endif; ?>
                <a href="orders.php" class="btn btn-secondary mt-3">Voltar para Lista de Pedidos</a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
