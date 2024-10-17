<?php
require_once '../config/config.php';
require_login(); // Verifica se o usuário está logado

// Aqui começa o conteúdo do dashboard, como obter informações
$total_sales_today = get_total_sales_today();
$open_orders = order_get_open_count();
$low_stock_products = get_low_stock_products();
$tables = get_all_tables();
$products = get_all_products();

$sales_data = [
    'Segunda' => 1200,
    'Terça' => 1500,
    'Quarta' => 1800,
    'Quinta' => 2000,
    'Sexta' => 2500,
    'Sábado' => 3000,
    'Domingo' => 2200
];

$days = array_keys($sales_data);
$sales_values = array_values($sales_data);

include '../includes/header.php';

?>
<style>
/* css para tornar texto branco e fundo escuro */
.text-white {
    color: #fff;
}

/* css para tornar texto preto e fundo claro */
.text-dark {
    color: #333;
}
</style>
<!-- partial -->
<div class="row">
    <div class="col-sm-12">
        <div class="home-tab">
            <div class="tab-content tab-content-basic">
                <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview">

                    <!-- Estatísticas Rápidas - Cards maiores e coloridos -->
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="statistics-details d-flex align-items-center justify-content-between">
                                <div class="card-stat bg-primary text-white rounded p-4 m-2">
                                    <p class="statistics-title" style="color:#fff;">Vendas Hoje</p>
                                    <h3 class="rate-percentage" style="color:#fff;">MZN
                                        <?php echo number_format($total_sales_today, 2, ',', '.'); ?></h3>
                                </div>
                                <div class="card-stat bg-success text-white rounded p-4 m-2">
                                    <p class="statistics-title" style="color:#fff;">Pedidos Abertos</p>
                                    <h3 class="rate-percentage" style="color:#fff;"><?php echo $open_orders; ?></h3>
                                </div>
                                <div class="card-stat bg-danger text-white rounded p-4 m-2">
                                    <p class="statistics-title" style="color:#fff;">Produtos com Estoque Baixo</p>
                                    <h3 class="rate-percentage" style="color:#fff;"><?php echo $low_stock_products; ?>
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ações Rápidas - Botões maiores e coloridos -->
                    <div class="row mt-4">
                        <div class="col-lg-8 d-flex flex-column">
                            <div class="row flex-grow">
                                <div class="col-12 grid-margin stretch-card">
                                    <div class="card card-rounded shadow">
                                        <div class="card-body">
                                            <h4 class="card-title card-title-dash">Ações Rápidas</h4>
                                            <div class="row mt-3">
                                                <div class="col-md-4 mb-3">
                                                    <a href="tables.php"
                                                        class="btn btn-lg btn-block btn-outline-primary d-flex align-items-center justify-content-center">
                                                        <i class="mdi mdi-table-large menu-icon"></i> Mesas
                                                    </a>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <a href="orders.php"
                                                        class="btn btn-lg btn-block btn-outline-success d-flex align-items-center justify-content-center">
                                                        <i class="mdi mdi-cart menu-icon"></i> Pedidos
                                                    </a>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <a href="products.php"
                                                        class="btn btn-lg btn-block btn-outline-info d-flex align-items-center justify-content-center">
                                                        <i class="mdi mdi-food menu-icon"></i> Produtos
                                                    </a>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <a href="reports.php"
                                                        class="btn btn-lg btn-block btn-outline-warning d-flex align-items-center justify-content-center">
                                                        <i class="mdi mdi-chart-bar menu-icon"></i> Relatórios
                                                    </a>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <a href="#"
                                                        class="btn btn-lg btn-block btn-outline-danger d-flex align-items-center justify-content-center"
                                                        data-bs-toggle="modal" data-bs-target="#newSaleModal">
                                                        <i class="mdi mdi-cash-register menu-icon"></i> Venda Rápida
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Vendas Semanais - Gráfico -->
                            <div class="col-12 grid-margin stretch-card">
                                <div class="card card-rounded shadow">
                                    <div class="card-body">
                                        <h4 class="card-title">Vendas Semanais</h4>
                                        <canvas id="salesChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Controle de Mesas -->
                        <div class="col-lg-4 d-flex flex-column">
                            <div class="col-12 grid-margin stretch-card">
                                <div class="card card-rounded shadow">
                                    <div class="card-body">
                                        <h4 class="card-title card-title-dash">Controle de Mesas</h4>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
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
                                                        <td>
                                                            <span
                                                                class="badge <?php echo ($table['status'] === 'ocupada') ? 'bg-danger' : 'bg-success'; ?>">
                                                                <?php echo $table['status']; ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <a href="tables.php?id=<?php echo $table['id']; ?>"
                                                                class="btn btn-sm btn-primary">Gerenciar</a>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Modal para Nova Venda -->
                    <div class="modal fade" id="newSaleModal" tabindex="-1" aria-labelledby="newSaleModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="newSaleModalLabel">Nova Venda</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="newSaleForm">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="product" class="form-label">Produto</label>
                                                <select class="form-select" id="product" required>
                                                    <?php foreach ($products as $product): ?>
                                                    <option value="<?php echo $product['id']; ?>"
                                                        data-price="<?php echo $product['price']; ?>">
                                                        <?php echo $product['name']; ?> - MZN
                                                        <?php echo number_format($product['price'], 2); ?>
                                                    </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label for="quantity" class="form-label">Quantidade</label>
                                                <input type="number" class="form-control" id="quantity" min="1"
                                                    value="1" required>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">&nbsp;</label>
                                                <button type="button" class="btn btn-primary form-control"
                                                    id="addItemButton">Adicionar</button>
                                            </div>
                                        </div>
                                    </form>
                                    <table class="table" id="saleItemsTable">
                                        <thead>
                                            <tr>
                                                <th>Produto</th>
                                                <th>Quantidade</th>
                                                <th>Preço Unitário</th>
                                                <th>Total</th>
                                                <th>Ação</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="3">Total da Venda</th>
                                                <th id="saleTotalAmount">MZN 0.00</th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                    <div class="mb-3">
                                        <label for="paymentMethod" class="form-label">Método de Pagamento</label>
                                        <select class="form-select" id="paymentMethod" required>
                                            <option value="cash">Dinheiro</option>
                                            <option value="card">Cartão</option>
                                            <option value="mpesa">M-Pesa</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Cancelar</button>
                                    <button type="button" class="btn btn-primary" id="finalizeSaleButton">Finalizar
                                        Venda</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Modal de Recibo -->
                <div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="receiptModalLabel">Recibo da Venda</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body" id="receiptContent">
                                <!-- O conteúdo do recibo será inserido aqui -->
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                <button type="button" class="btn btn-primary" id="printReceiptButton">Imprimir</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.bundle.min.js">
        </script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
        <script src="../public/custom.js"></script>

    </div>
</div>
<?php include '../includes/footer.php'; ?>