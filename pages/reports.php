<?php
require_once '../config/config.php';
require_login();

// Obter dados para os relatórios
$total_sales = get_total_sales();
$total_orders = get_total_orders();
$total_clients = get_total_clients();
$monthly_sales = get_monthly_sales(); // Exemplo de função para obter vendas mensais
$category_sales = get_category_sales(); // Exemplo de função para obter vendas por categoria

include '../includes/header.php';
?>

<!-- Gráficos e Resumos de Relatórios -->
<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title text-center" style="color: #FFA500;">Relatórios</h4>
                <p class="text-muted text-center">Resumo das principais métricas de desempenho</p>

                <div class="row mt-4 text-center">
                    <!-- Total de Vendas -->
                    <div class="col-md-4">
                        <div class="card bg-success text-white mb-4">
                            <div class="card-body">
                                <i class="mdi mdi-cash-multiple" style="font-size: 2.5rem;"></i>
                                <h5 class="card-title mt-2">Total de Vendas</h5>
                                <p class="card-text" style="font-size: 1.5rem;">MZN <?php echo number_format($total_sales, 2, ',', '.'); ?></p>
                            </div>
                        </div>
                    </div>
                    <!-- Total de Pedidos -->
                    <div class="col-md-4">
                        <div class="card bg-primary text-white mb-4">
                            <div class="card-body">
                                <i class="mdi mdi-receipt" style="font-size: 2.5rem;"></i>
                                <h5 class="card-title mt-2">Total de Pedidos</h5>
                                <p class="card-text" style="font-size: 1.5rem;"><?php echo $total_orders; ?></p>
                            </div>
                        </div>
                    </div>
                    <!-- Total de Clientes -->
                    <div class="col-md-4">
                        <div class="card bg-warning text-white mb-4">
                            <div class="card-body">
                                <i class="mdi mdi-account-multiple" style="font-size: 2.5rem;"></i>
                                <h5 class="card-title mt-2">Total de Clientes</h5>
                                <p class="card-text" style="font-size: 1.5rem;"><?php echo $total_clients; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráficos -->
                <div class="row">
                    <!-- Gráfico de Vendas Mensais -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Vendas Mensais</h5>
                                <canvas id="monthlySalesChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <!-- Gráfico de Vendas por Categoria -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Vendas por Categoria</h5>
                                <canvas id="categorySalesChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Outros Gráficos ou Relatórios -->
                <!-- Você pode adicionar mais relatórios como vendas por produto, etc. -->
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<?php include '../includes/footer.php'; ?>

<!-- Bibliotecas de gráficos (Chart.js) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Dados e configuração do gráfico de Vendas Mensais
var ctx = document.getElementById('monthlySalesChart').getContext('2d');
var monthlySalesChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: [<?php echo implode(',', array_keys($monthly_sales)); ?>], // Mêses
        datasets: [{
            label: 'Vendas (MZN)',
            data: [<?php echo implode(',', array_values($monthly_sales)); ?>],
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Dados e configuração do gráfico de Vendas por Categoria
var ctx2 = document.getElementById('categorySalesChart').getContext('2d');
var categorySalesChart = new Chart(ctx2, {
    type: 'pie',
    data: {
        labels: [<?php echo implode(',', array_keys($category_sales)); ?>], // Categorias
        datasets: [{
            label: 'Vendas por Categoria',
            data: [<?php echo implode(',', array_values($category_sales)); ?>],
            backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'],
        }]
    },
    options: {
        responsive: true,
    }
});
</script>
