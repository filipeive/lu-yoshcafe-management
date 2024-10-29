<?php
require_once '../config/config.php';
require_login();

$pageTitle = "Pedidos";
include '../includes/header.php';

// Helper functions for StarAdmin2 specific icons and classes
function get_status_class_staradmin($status) {
    $classes = [
        'completed' => 'badge-success',
        'active' => 'badge-warning',
        'cancelled' => 'badge-danger',
        'processing' => 'badge-info'
    ];
    return $classes[$status] ?? 'badge-secondary';
}

// Pagination setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 6;
$offset = ($page - 1) * $per_page;

// Get total number of orders and paginated orders
$total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$total_pages = ceil($total_orders / $per_page);
$orders = get_paginated_order($offset, $per_page);
?>
<style></style>
<link href="assets/order.css" rel="stylesheet" type="text/css">
<div class="content-wrapper">
    <div class="row">
        <!-- Stats Cards Row -->
        <div class="col-sm-12 col-lg-3 grid-margin stretch-card">
            <div class="card card-rounded">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h4 class="card-title card-title-dash">Total Pedidos</h4>
                        <i class="mdi mdi-receipt text-primary icon-md"></i>
                    </div>
                    <div class="mt-3">
                        <h2 class="rate-percentage"><?php echo $total_orders; ?></h2>
                        <p class="text-success mb-0">
                            <i class="mdi mdi-trending-up"></i> Total acumulado
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-12 col-lg-3 grid-margin stretch-card">
            <div class="card card-rounded">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h4 class="card-title card-title-dash">Pedidos Hoje</h4>
                        <i class="mdi mdi-calendar-today text-success icon-md"></i>
                    </div>
                    <div class="mt-3">
                        <h2 class="rate-percentage"><?php echo order_get_total_today() ; ?></h2>
                        <p class="text-success mb-0">
                            <i class="mdi mdi-clock"></i> Hoje
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-12 col-lg-3 grid-margin stretch-card">
            <div class="card card-rounded">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h4 class="card-title card-title-dash">Valor Total</h4>
                        <i class="mdi mdi-cash-multiple text-info icon-md"></i>
                    </div>
                    <div class="mt-3">
                        <h2 class="rate-percentage">MZN <?php echo number_format(order_get_total_today(), 2); ?></h2>
                        <p class="text-info mb-0">
                            <i class="mdi mdi-chart-line"></i> Total em pedidos
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-12 col-lg-3 grid-margin stretch-card">
            <div class="card card-rounded">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h4 class="card-title card-title-dash">Ativos</h4>
                        <i class="mdi mdi-clock-alert text-warning icon-md"></i>
                    </div>
                    <div class="mt-3">
                        <h2 class="rate-percentage"><?php echo order_get_open_count(); ?></h2>
                        <p class="text-warning mb-0">
                            <i class="mdi mdi-alert-circle"></i> Aguardando finalização
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card card-rounded">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title">Lista de Pedidos</h4>
                        <div class="d-flex align-items-center">
                            <div class="input-group" style="width: 250px;">
                                <input type="text" class="form-control" placeholder="Pesquisar pedidos..."
                                    id="ordersSearch">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="button">
                                        <i class="mdi mdi-magnify"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-primary ms-3"
                                onclick="window.location.href='create_order.php'">
                                <i class="mdi mdi-plus"></i> Novo Pedido
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Data</th>
                                    <th>Mesa</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td class="text-muted">#<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?>
                                    </td>
                                    <td>
                                        <div class="d-flex">
                                            <i class="mdi mdi-calendar me-2 text-primary"></i>
                                            <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                                        </div>
                                    </td>
                                    <td>Mesa <?php echo $order['table_id']; ?></td>
                                    <td class="font-weight-bold">MZN
                                        <?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td>
                                        <div class="badge <?php echo get_status_class_staradmin($order['status']); ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-outline-primary btn-icon btn-rounded"
                                                onclick="window.location.href='view_order.php?id=<?php echo $order['id']; ?>'">
                                                <i class="mdi mdi-eye"></i>
                                            </button>
                                            <?php if ($order['status'] != 'completed'): ?>
                                            <button class="btn btn-outline-info btn-icon btn-rounded"
                                                onclick="window.location.href='edit_order.php?id=<?php echo $order['id']; ?>'">
                                                <i class="mdi mdi-pencil"></i>
                                            </button>
                                            <button class="btn btn-outline-success btn-icon btn-rounded"
                                                onclick="completeOrder(<?php echo $order['id']; ?>)">
                                                <i class="mdi mdi-check"></i>
                                            </button>
                                            <?php endif; ?>
                                            <div class="dropdown">
                                                <button class="btn btn-outline-secondary btn-icon btn-rounded"
                                                    type="button" data-bs-toggle="dropdown">
                                                    <i class="mdi mdi-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item"
                                                            href="print_order.php?id=<?php echo $order['id']; ?>">
                                                            <i class="mdi mdi-printer me-2"></i> Imprimir
                                                        </a></li>
                                                    <li><a class="dropdown-item" href="#">
                                                            <i class="mdi mdi-file-pdf me-2"></i> Exportar PDF
                                                        </a></li>
                                                    <?php if ($order['status'] != 'completed'): ?>
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <li><a class="dropdown-item text-danger" href="#"
                                                            onclick="cancelOrder(<?php echo $order['id']; ?>)">
                                                            <i class="mdi mdi-delete me-2"></i> Cancelar
                                                        </a></li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <p class="text-muted mb-0">
                            Mostrando <?php echo ($offset + 1); ?> até
                            <?php echo min($offset + $per_page, $total_orders); ?> de <?php echo $total_orders; ?>
                            registros
                        </p>
                        <nav>
                            <ul class="pagination pagination-rounded">
                                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>">
                                        <i class="mdi mdi-chevron-left"></i>
                                    </a>
                                </li>
                                <?php for($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>">
                                        <i class="mdi mdi-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function completeOrder(orderId) {
        Swal.fire({
            title: 'Finalizar Pedido',
            text: 'Tem certeza que deseja finalizar este pedido e gerar uma venda?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sim, finalizar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Envia solicitação para completar o pedido e gerar a venda
                fetch('gerir_pedidos/complete_order.php?id=' + orderId)
                    .then(response => response.json())
                    .then(data => {
                        // Verifica se a resposta contém 'success' como true
                        if (data && data.success) {
                            Swal.fire({
                                title: 'Pedido Finalizado!',
                                text: 'Deseja imprimir o recibo?',
                                icon: 'success',
                                showCancelButton: true,
                                confirmButtonText: 'Sim, imprimir',
                                cancelButtonText: 'Não'
                            }).then((printResult) => {
                                if (printResult.isConfirmed) {
                                    // Abre o recibo em uma nova janela
                                    window.open('print_order.php?id=' + orderId, 'receipt',
                                        'width=400,height=600');
                                }
                                // Recarrega a página para atualizar a lista de pedidos
                                window.location.reload();
                            });
                        } else {
                            // Mostra mensagem de erro se 'data.success' não for true
                            Swal.fire('Erro!', data.message || 'Erro ao finalizar pedido', 'error');
                            window.location.reload()
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('Erro!', 'Erro ao processar requisição', 'error');
                    });
            }
        });
    }
    function cancelOrder(orderId) {
        Swal.fire({
            title: 'Cancelar Pedido',
            text: 'Tem certeza que deseja cancelar este pedido?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sim, cancelar',
            cancelButtonText: 'Não',
            confirmButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'gerir_pedidos/cancel_order.php?id=' + orderId;
            }
        });
    }
    </script>
    <?php include '../includes/footer.php';?>