<?php
require_once '../config/config.php';
require_login();

// Obter dados para os relatórios
$total_sales = get_total_sales();
$total_orders = get_total_orders();
$total_clients = get_total_clients();
$monthly_sales = get_monthly_sales(); // Função que retorna array de vendas mensais
$weekly_sales = get_weekly_sales(); // Função que retorna array de vendas semanais

// Prepare os dados para JSON
$monthly_sales_json = json_encode($monthly_sales);
$weekly_sales_json = json_encode($weekly_sales);
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
                                <p class="card-text" style="font-size: 1.5rem;">MZN
                                    <?php echo number_format($total_sales, 2, ',', '.'); ?></p>
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
                                <div class="chart-container" style="position: relative; height:300px;">
                                    <canvas id="monthlySalesChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Sales Chart -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Vendas Semanais</h4>
                                <div class="chart-container" style="position: relative; height:300px;">
                                    <canvas id="salesChart"></canvas>
                                </div>
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
<!-- Adicione antes do script -->
<input type="hidden" id="monthlySalesData" value='<?php echo $monthly_sales_json; ?>'>
<input type="hidden" id="weeklySalesData" value='<?php echo $weekly_sales_json; ?>'>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gráfico Mensal
    const monthlySalesData = JSON.parse(document.getElementById('monthlySalesData').value);
    const ctxMonthly = document.getElementById('monthlySalesChart').getContext('2d');
    new Chart(ctxMonthly, {
        type: 'bar',
        data: {
            labels: Object.keys(monthlySalesData),
            datasets: [{
                label: 'Vendas (MZN)',
                data: Object.values(monthlySalesData),
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

    // Gráfico Semanal
    const weeklySalesData = JSON.parse(document.getElementById('weeklySalesData').value);
    const ctxWeekly = document.getElementById('salesChart').getContext('2d');
    new Chart(ctxWeekly, {
        type: 'line',
        data: {
            labels: Object.keys(weeklySalesData),
            datasets: [{
                label: 'Vendas (MZN)',
                data: Object.values(weeklySalesData),
                borderColor: '#4B49AC',
                backgroundColor: 'rgba(75, 73, 172, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        borderDash: [2, 2]
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});
</script>