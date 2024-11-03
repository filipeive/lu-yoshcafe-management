<?php
require_once '../config/config.php';
require_login();

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$order = order_get_by_id($order_id);
$order_items = order_get_items($order_id);

if (!$order) {
    $_SESSION['error_message'] = "Pedido não encontrado.";
    header('Location: orders.php');
    exit;
}

include '../includes/header.php';

// Função helper para o status do pedido
function getStatusBadge($status) {
    switch ($status) {
        case 'completed':
            return '<span class="badge bg-success"><i class="mdi mdi-check-circle me-1"></i>Concluído</span>';
        case 'cancelled':
            return '<span class="badge bg-danger"><i class="mdi mdi-close-circle me-1"></i>Cancelado</span>';
        default:
            return '<span class="badge bg-primary"><i class="mdi mdi-clock-outline me-1"></i>Ativo</span>';
    }
}
?>


<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="card-title mb-0">
                            <i class="mdi mdi-clipboard-text me-2"></i>
                            Pedido #<?php echo $order['id']; ?>
                        </h4>
                        <small class="text-muted">Criado em
                            <?php echo date('d/m/Y \à\s H:i', strtotime($order['created_at'])); ?></small>
                    </div>
                    <div>
                        <?php echo getStatusBadge($order['status']); ?>
                    </div>
                </div>

                <div class="row">
                    <!-- Informações do Pedido -->
                    <div class="col-md-4 mb-4">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="mdi mdi-information me-2"></i>
                                    Informações do Pedido
                                </h5>
                                <div class="mt-4">
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="text-muted">Mesa</span>
                                        <span class="badge bg-info">
                                            <i class="mdi mdi-table-furniture me-1"></i>
                                            Mesa <?php echo $order['table_number']; ?>
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="text-muted">Data</span>
                                        <span>
                                            <i class="mdi mdi-calendar me-1"></i>
                                            <?php echo date('d/m/Y', strtotime($order['created_at'])); ?>
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Hora</span>
                                        <span>
                                            <i class="mdi mdi-clock-outline me-1"></i>
                                            <?php echo date('H:i', strtotime($order['created_at'])); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sumário Financeiro -->
                    <div class="col-md-8 mb-4">
                        <div class="card border-0 bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title text-white">
                                    <i class="mdi mdi-cash-multiple me-2"></i>
                                    Sumário Financeiro
                                </h5>
                                <div class="row mt-4">
                                    <div class="col-md-4">
                                        <div class="d-flex flex-column">
                                            <small>Total do Pedido</small>
                                            <h3 class="mb-0">MZN
                                                <?php echo number_format($order['total_amount'], 2, ',', '.'); ?></h3>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-flex flex-column">
                                            <small>Qtd. Itens</small>
                                            <h3 class="mb-0"><?php echo count($order_items); ?></h3>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-flex flex-column">
                                            <small>Média por Item</small>
                                            <h3 class="mb-0">MZN
                                                <?php echo number_format($order['total_amount'] / count($order_items), 2, ',', '.'); ?>
                                            </h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabela de Itens -->
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="mdi mdi-food-fork-drink me-2"></i>
                                    Itens do Pedido
                                </h5>
                                <div class="table-responsive mt-4">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Produto</th>
                                                <th class="text-center">Quantidade</th>
                                                <th class="text-end">Preço Unitário</th>
                                                <th class="text-end">Subtotal</th>
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
                                                <td class="text-center">
                                                    <span class="badge bg-info"><?php echo $item['quantity']; ?></span>
                                                </td>
                                                <td class="text-end">
                                                    MZN
                                                    <?php echo number_format($item['product_price'], 2, ',', '.'); ?>
                                                </td>
                                                <td class="text-end">
                                                    <strong>MZN
                                                        <?php echo number_format($item['quantity'] * $item['product_price'], 2, ',', '.'); ?></strong>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="3" class="text-end">
                                                    <strong>Total:</strong>
                                                </td>
                                                <td class="text-end">
                                                    <strong class="text-primary">
                                                        MZN
                                                        <?php echo number_format($order['total_amount'], 2, ',', '.'); ?>
                                                    </strong>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botões de Ação -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <a href="orders.php" class="btn btn-outline-secondary">
                                            <i class="mdi mdi-arrow-left me-2"></i>
                                            Voltar para Lista
                                        </a>
                                    </div>
                                    <div>
                                        <?php if ($order['status'] != 'completed' && $order['status'] != 'canceled'): ?>
                                        <a href="gerir_pedidos/complete_order.php?id=<?php echo $order['id']; ?>"
                                            class="btn btn-success me-2"
                                            onclick="return confirm('Tem certeza que deseja finalizar este pedido?')">
                                            <i class="mdi mdi-check-circle me-2"></i>
                                            Finalizar Pedido
                                        </a>
                                        <a href="edit_order.php?id=<?php echo $order['id']; ?>" class="btn btn-primary">
                                            <i class="mdi mdi-pencil me-2"></i>
                                            Editar Pedido
                                        </a>
                                        <?php endif; ?>
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


<?php include '../includes/footer.php'; ?>