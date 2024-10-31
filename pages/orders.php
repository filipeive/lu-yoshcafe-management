<?php
require_once '../config/config.php';
require_login();

$pageTitle = "Pedidos";
include '../includes/header.php';

// Helper functions
function get_status_class_staradmin($status) {
    $classes = [
        'completed' => 'bg-success text-white',
        'active' => 'bg-warning text-white',
        'canceled' => 'bg-danger text-white',
        'paid' => 'bg-info text-white'
    ];
    return $classes[$status] ?? 'bg-secondary text-white';
}

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Modified pagination setup with search
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 8;
$offset = ($page - 1) * $per_page;

// Modified query to include search
$search_condition = '';
if (!empty($search)) {
    $search_condition = " WHERE id LIKE :search 
                         OR table_id LIKE :search 
                         OR status LIKE :search 
                         OR DATE_FORMAT(created_at, '%d/%m/%Y') LIKE :search";
}

// Get total number of orders with search
$total_query = "SELECT COUNT(*) FROM orders" . $search_condition;
$stmt = $pdo->prepare($total_query);
if (!empty($search)) {
    $search_param = "%$search%";
    $stmt->bindParam(':search', $search_param);
}
$stmt->execute();
$total_orders = $stmt->fetchColumn();

// Get paginated orders with search
$orders_query = "SELECT * FROM orders" . $search_condition . " ORDER BY created_at DESC LIMIT :offset, :per_page";
$stmt = $pdo->prepare($orders_query);
if (!empty($search)) {
    $search_param = "%$search%";
    $stmt->bindParam(':search', $search_param);
}
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':per_page', $per_page, PDO::PARAM_INT);
$stmt->execute();
$orders = $stmt->fetchAll();

$total_pages = ceil($total_orders / $per_page);
?>

<!-- Custom CSS -->
<style>
.stat-card {
    transition: transform 0.2s, box-shadow 0.2s;
    border: none;
    border-radius: 15px;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.stat-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
}

.orders-table {
    border-radius: 15px;
    overflow: hidden;
}

.table> :not(caption)>*>* {
    padding: 1rem 1.5rem;
}

.status-badge {
    padding: 8px 12px;
    border-radius: 8px;
    font-weight: 500;
    font-size: 0.85rem;
}

.action-btn {
    width: 35px;
    height: 35px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    transition: all 0.2s;
}

.action-btn:hover {
    transform: translateY(-2px);
}

.search-container {
    position: relative;
    max-width: 350px;
}

.search-container .search-icon {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
}

.search-input {
    padding-left: 45px;
    border-radius: 10px;
    border: 1px solid #dee2e6;
}

.action-buttons {
    display: flex;
    gap: 8px;
    align-items: center;
}

.action-btn {
    width: auto;
    height: 35px;
    padding: 0 12px;
    display: flex;
    align-items: center;
    gap: 8px;
    justify-content: center;
    border-radius: 8px;
    transition: all 0.2s;
    font-size: 0.875rem;
}

.action-btn i {
    font-size: 1.1rem;
}

.action-btn:hover {
    transform: translateY(-2px);
}

.no-results {
    text-align: center;
    padding: 2rem;
    color: #6c757d;
}
</style>

<div class="row g-4">
    <!-- Stats Cards Row -->
    <div class="col-sm-12 col-lg-3 grid-margin stretch-card">
        <div class="card card-rounded border-start border-primary border-4">
            <div class="card-body pb-0">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="card-title card-title-dash text-muted mb-1">Total Pedidos</h6>
                        <h2 class="rate-percentage text-primary mb-2"><?php echo $total_orders; ?></h2>
                        <p class="text-primary d-flex align-items-center mb-3">
                            <i class="mdi mdi-receipt me-1"></i>
                            <span>Pedidos acumulados</span>
                        </p>
                    </div>
                    <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                        <i class="mdi mdi-receipt text-primary icon-md m-0"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-12 col-lg-3 grid-margin stretch-card">
        <div class="card card-rounded border-start border-success border-4">
            <div class="card-body pb-0">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="card-title card-title-dash text-muted mb-1">Pedidos Hoje</h6>
                        <h2 class="rate-percentage text-success mb-2"><?php echo order_get_total_today(); ?></h2>
                        <p class="text-success d-flex align-items-center mb-3">
                            <i class="mdi mdi-calendar-today me-1"></i>
                            <span>Hoje</span>
                        </p>
                    </div>
                    <div class="bg-success bg-opacity-10 rounded-circle p-3">
                        <i class="mdi mdi-calendar-today text-success icon-md m-0"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-12 col-lg-3 grid-margin stretch-card">
        <div class="card card-rounded border-start border-info border-4">
            <div class="card-body pb-0">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="card-title card-title-dash text-muted mb-1">Valor Total</h6>
                        <h2 class="rate-percentage text-info mb-2">MZN
                            <?php echo number_format(order_get_total_today(), 2); ?></h2>
                        <p class="text-info d-flex align-items-center mb-3">
                            <i class="mdi mdi-cash-multiple me-1"></i>
                            <span>Total do dia</span>
                        </p>
                    </div>
                    <div class="bg-info bg-opacity-10 rounded-circle p-3">
                        <i class="mdi mdi-cash-multiple text-info icon-md m-0"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-12 col-lg-3 grid-margin stretch-card">
        <div class="card card-rounded border-start border-warning border-4">
            <div class="card-body pb-0">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="card-title card-title-dash text-muted mb-1">Pedidos Ativos</h6>
                        <h2 class="rate-percentage text-warning mb-2"><?php echo order_get_open_count(); ?></h2>
                        <p class="text-warning d-flex align-items-center mb-3">
                            <i class="mdi mdi-clock-alert me-1"></i>
                            <span>Em andamento</span>
                        </p>
                    </div>
                    <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                        <i class="mdi mdi-clock-alert text-warning icon-md m-0"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Tabela de Pedidos (Orders Table) no Estilo de Vendas -->
    <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
    </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger">
        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
    </div>
    <?php endif; ?>

    <div class="col-lg-12 grid-margin">
        <div class="card card-rounded shadow-sm">
            <div class="card-body">
                <!-- Cabeçalho -->
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title mb-0 me-2">Lista de Pedidos</h4>
                        <span class="badge bg-primary rounded-pill"><?php echo $total_orders; ?> pedidos</span>
                    </div>
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <!-- Barra de Pesquisa -->
                        <div class="search-field d-none d-md-flex">
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0">
                                    <i class="mdi mdi-magnify text-primary"></i>
                                </span>
                                <input type="text" class="form-control bg-transparent border-start-0 ps-0"
                                    placeholder="Pesquisar pedidos..." id="ordersSearch"
                                    value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabela -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th class="py-3">ID</th>
                                <th class="py-3">Data</th>
                                <th class="py-3">Mesa</th>
                                <th class="py-3">Total</th>
                                <th class="py-3">Status</th>
                                <th class="py-3 text-center" width="200">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    <i class="mdi mdi-alert-circle-outline mb-2" style="font-size: 2rem;"></i>
                                    <p class="mb-0">Nenhum pedido encontrado</p>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                            <tr>
                                <td class="py-3">
                                    <span
                                        class="fw-medium text-primary">#<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?></span>
                                </td>
                                <td class="py-3">
                                    <div class="d-flex align-items-center">
                                        <span class="bg-primary bg-opacity-10 p-2 rounded me-2">
                                            <i class="mdi mdi-calendar text-primary"></i>
                                        </span>
                                        <div>
                                            <div class="fw-medium">
                                                <?php echo date('d/m/Y', strtotime($order['created_at'])); ?></div>
                                            <small
                                                class="text-muted"><?php echo date('H:i', strtotime($order['created_at'])); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3">
                                    <span class="badge bg-light text-dark">Mesa <?php echo $order['table_id']; ?></span>
                                </td>
                                <td class="py-3">
                                    <div class="fw-medium">MZN <?php echo number_format($order['total_amount'], 2); ?>
                                    </div>
                                </td>
                                <td class="py-3">
                                    <div
                                        class="badge <?php echo get_status_class_staradmin($order['status']); ?> rounded-pill px-3">
                                        <i class="mdi mdi-circle-medium me-1"></i>
                                        <?php echo ucfirst($order['status']); ?>
                                    </div>
                                </td>
                                <td class="py-3">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="view_order.php?id=<?php echo $order['id']; ?>"
                                            class="btn btn-primary btn-icon btn-sm" data-bs-toggle="tooltip"
                                            title="Ver Detalhes">
                                            <i class="mdi mdi-eye"></i>
                                        </a>
                                        <a href="print_order.php?id=<?php echo $order['id']; ?>"
                                            class="btn btn-info btn-icon btn-sm" data-bs-toggle="tooltip"
                                            title="Imprimir">
                                            <i class="mdi mdi-printer"></i>
                                        </a>
                                        <?php if ($order['status'] != 'completed'): ?>
                                        <a href="edit_order.php?id=<?php echo $order['id']; ?>"
                                            class="btn btn-warning btn-icon btn-sm" data-bs-toggle="tooltip"
                                            title="Editar">
                                            <i class="mdi mdi-pencil"></i>
                                        </a>
                                        <button class="btn btn-success btn-icon btn-sm" data-bs-toggle="tooltip"
                                            title="Finalizar" onclick="completeOrder(<?php echo $order['id']; ?>)">
                                            <i class="mdi mdi-check"></i>
                                        </button>
                                        <button class="btn btn-danger btn-icon btn-sm" data-bs-toggle="tooltip"
                                            title="Cancelar" onclick="cancelOrder(<?php echo $order['id']; ?>)">
                                            <i class="mdi mdi-close"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginação -->
                <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-3">
                    <div class="text-muted">
                        Mostrando <span class="fw-medium"><?php echo ($offset + 1); ?></span> até
                        <span class="fw-medium"><?php echo min($offset + $per_page, $total_orders); ?></span> de
                        <span class="fw-medium"><?php echo $total_orders; ?></span> registros
                    </div>
                    <nav>
                        <ul class="pagination pagination-rounded mb-0">
                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link"
                                    href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">
                                    <i class="mdi mdi-chevron-left"></i>
                                </a>
                            </li>
                            <?php for($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link"
                                    href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                <a class="page-link"
                                    href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">
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
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
// Função de pesquisa em tempo real
document.getElementById('ordersSearch').addEventListener('input', function(e) {
    const searchTerm = e.target.value;
    // Adiciona um pequeno delay para evitar muitas requisições
    clearTimeout(this.searchTimeout);
    this.searchTimeout = setTimeout(() => {
        window.location.href = 'orders.php?search=' + encodeURIComponent(searchTerm);
    }, 500);
});

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
                            // Redireciona para orders.php após a confirmação
                            window.location.href = 'orders.php';
                        });
                    } else {
                        // Mostra mensagem de erro se 'data.success' não for true
                        Swal.fire('Erro!', data.message || 'Erro ao finalizar pedido', 'error');
                        window.location.href = 'orders.php';
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
            fetch(`gerir_pedidos/cancel_order.php?id=${orderId}`, {
                    method: 'GET'
                })
                .then(response => response.json())
                .then(data => {
                    console.log(data); // Verifique a resposta no console

                    if (data.success) {
                        Swal.fire('Cancelado!', 'O pedido foi cancelado com sucesso.', 'success')
                            .then(() => location.reload());
                    } else {
                        Swal.fire('Erro', data.error || 'Erro ao cancelar o pedido.', 'error');
                    }
                })
                .catch(error => {
                    console.error('Erro na requisição:', error);
                    Swal.fire('Erro', 'Erro na comunicação com o servidor.', 'error');
                });
        }
    });
}
</script>
<?php include '../includes/footer.php';?>