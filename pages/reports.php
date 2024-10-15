<?php
require_once '../config/config.php';
require_login();
// Obter dados para os relatórios
$total_sales = get_total_sales();
$total_orders = get_total_orders();
$total_clients = get_total_clients();

include '../includes/header.php';
?>

<div class="row">
    <div class="col-sm-12">
        <div class="home-tab">
            <div class="d-sm-flex align-items-center justify-content-between border-bottom">
                <h4 class="card-title">Relatórios</h4>
            </div>
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Total de Vendas</h5>
                            <p class="card-text">MZN <?php echo number_format($total_sales, 2, ',', '.'); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Total de Pedidos</h5>
                            <p class="card-text"><?php echo $total_orders; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Total de Clientes</h5>
                            <p class="card-text"><?php echo $total_clients; ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Adicione mais relatórios conforme necessário -->
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>