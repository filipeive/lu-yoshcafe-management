<?php
require_once '../config/config.php';
require_login();
require_admin();

// Obter informações para o dashboard
$total_sales_today = get_total_sales_today();
$open_orders = get_open_orders();
$low_stock_products = get_low_stock_products();
$tables = get_all_tables();
$products = get_all_products();

include '../includes/header.php';
?>

<!-- partial -->
<div class="row">
    <div class="col-sm-12">
        <div class="home-tab">
            <div class="tab-content tab-content-basic">
                <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview">
                    <div class="row">
                        <!-- Estatísticas Rápidas -->
                        <div class="col-sm-12">
                            <div class="statistics-details d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="statistics-title">Vendas Hoje</p>
                                    <h3 class="rate-percentage">MZN
                                        <?php echo number_format($total_sales_today, 2, ',', '.'); ?></h3>
                                </div>
                                <div>
                                    <p class="statistics-title">Pedidos Abertos</p>
                                    <h3 class="rate-percentage"><?php echo $open_orders; ?></h3>
                                </div>
                                <div>
                                    <p class="statistics-title">Produtos com Estoque Baixo</p>
                                    <h3 class="rate-percentage"><?php echo $low_stock_products; ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ações Rápidas -->
                    <div class="row mt-4">
                        <div class="col-lg-8 d-flex flex-column">
                            <div class="row flex-grow">
                                <div class="col-12 grid-margin stretch-card">
                                    <div class="card card-rounded">
                                        <div class="card-body">
                                            <h4 class="card-title card-title-dash">Ações Rápidas</h4>
                                            <div class="row mt-3">
                                                <div class="col-md-4 mb-3">
                                                    <a href="tables.php" class="btn btn-primary btn-lg btn-block">
                                                        <i class="mdi mdi-table-large menu-icon"></i> Mesas
                                                    </a>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <a href="orders.php" class="btn btn-success btn-lg btn-block">
                                                        <i class="mdi mdi-cart menu-icon"></i> Pedidos
                                                    </a>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <a href="products.php" class="btn btn-info btn-lg btn-block">
                                                        <i class="mdi mdi-food menu-icon"></i> Produtos
                                                    </a>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <a href="reports.php" class="btn btn-warning btn-lg btn-block">
                                                        <i class="mdi mdi-chart-bar menu-icon"></i> Relatórios
                                                    </a>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <a href="#" class="btn btn-danger btn-lg btn-block"
                                                        data-bs-toggle="modal" data-bs-target="#quickSaleModal">
                                                        <i class="mdi mdi-cash-register menu-icon"></i> Venda Rápida
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Controle de Mesas -->
                        <div class="col-lg-4 d-flex flex-column">
                            <div class="row flex-grow">
                                <div class="col-12 grid-margin stretch-card">
                                    <div class="card card-rounded">
                                        <div class="card-body">
                                            <h4 class="card-title card-title-dash">Controle de Mesas</h4>
                                            <div class="table-responsive">
                                                <table class="table">
                                                    <thead>
                                                        <tr>
                                                            <th>Mesa</th>
                                                            <th>Status</th>
                                                            <th>Ação</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($tables as $table): ?>
                                                        <tr>
                                                            <td><?php echo $table['number']; ?></td>
                                                            <td><?php echo $table['status']; ?></td>
                                                            <td><a href="manage_table.php?id=<?php echo $table['id']; ?>"
                                                                    class="btn btn-sm btn-primary">Gerenciar</a></td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Geração de Recibos -->
                                <div class="col-12 grid-margin stretch-card">
                                    <div class="card card-rounded">
                                        <div class="card-body">
                                            <h4 class="card-title card-title-dash">Geração de Recibos</h4>
                                            <form id="generateReceiptForm">
                                                <div class="mb-3">
                                                    <label for="order_id" class="form-label">ID do Pedido</label>
                                                    <input type="text" class="form-control" id="order_id" required>
                                                </div>
                                                <button type="button" class="btn btn-primary"
                                                    id="generateReceiptSubmit">Gerar Recibo</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal de Venda Rápida -->
                    <div class="modal fade" id="quickSaleModal" tabindex="-1" aria-labelledby="quickSaleModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="quickSaleModalLabel">Venda Rápida</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="quickSaleForm">
                                        <div class="mb-3">
                                            <label for="product" class="form-label">Produto</label>
                                            <select class="form-select" id="product" required>
                                                <?php foreach ($products as $product): ?>
                                                <option value="<?php echo $product['id']; ?>">
                                                    <?php echo $product['name']; ?> - MZN
                                                    <?php echo $product['price']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="quantity" class="form-label">Quantidade</label>
                                            <input type="number" class="form-control" id="quantity" min="1" value="1"
                                                required>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Cancelar</button>
                                    <button type="button" class="btn btn-primary" id="quickSaleSubmit">Finalizar
                                        Venda</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                    document.getElementById('quickSaleSubmit').addEventListener('click', function() {
                        var product = document.getElementById('product').value;
                        var quantity = document.getElementById('quantity').value;

                        fetch('<?php echo BASE_URL; ?>/actions/process_quick_sale.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: 'product=' + product + '&quantity=' + quantity
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    alert('Venda realizada com sucesso!');
                                    location.reload(); // Recarregar para atualizar as estatísticas
                                } else {
                                    alert('Erro ao realizar a venda: ' + data.message);
                                }
                            })
                            .catch((error) => {
                                console.error('Error:', error);
                                alert('Erro ao processar a venda.');
                            });
                    });

                    document.getElementById('generateReceiptSubmit').addEventListener('click', function() {
                        var orderId = document.getElementById('order_id').value;

                        if (!orderId) {
                            alert('Por favor, insira o ID do pedido.');
                            return;
                        }

                        fetch('<?php echo BASE_URL; ?>/actions/generate_receipt.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: 'order_id=' + orderId
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Aqui você pode abrir o recibo em uma nova janela ou guia
                                    window.open(data.receipt_url, '_blank');
                                } else {
                                    alert('Erro ao gerar o recibo: ' + data.message);
                                }
                            })
                            .catch((error) => {
                                console.error('Error:', error);
                                alert('Erro ao gerar o recibo.');
                            });
                    });
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>