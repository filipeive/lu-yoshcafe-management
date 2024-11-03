<?php
require_once '../config/config.php';
require_login();

// Obter dados para os relatórios
$total_sales = get_total_sales();
$total_orders = get_total_orders();
$monthly_sales = get_monthly_sales();
$weekly_sales = get_weekly_sales();

// Preparar os dados para JSON
$monthly_sales_json = json_encode($monthly_sales);
$weekly_sales_json = json_encode($weekly_sales);


// Debug (temporário)
error_log('Monthly sales: ' . print_r($monthly_sales, true));
error_log('Weekly sales: ' . print_r($weekly_sales, true));
include '../includes/header.php';
?>

<head>
    <link rel="stylesheet" type="text/css" href="assets/reports.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
</head>
<!--Header do Painel de Controle-->
<div class="row" style="margin-top: 2px; color:#000">
    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card card-rounded border-start border-primary border-4">
                <div class="card-body pb-0">
                    <div class="d-flex align-items-center justify-content-between">
                        <i class="ti-dashboard icon-lg me-3"></i>
                        <div>
                            <h4 class="mb-1 text-warning">Bem-vindo ao Painel de Relatorios</h4>
                            <p class="mb-0">Acompanhe seus indicadores em tempo real</p>
                        </div>
                        <div class="ms-auto">
                            <p class="mb-1 mt-3 font-weight-semibold"><?php echo $usuario['name']; ?></p>
                            <span class="badge bg-white text-primary"><?php echo date('d/m/Y'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row">
        <!-- Total Sales Card -->
        <div class="col-sm-6 col-lg-3 grid-margin stretch-card">
            <div class="card card-rounded border-start border-primary border-4">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="circle-progress-width">
                            <i class="ti-money icon-md text-primary"></i>
                        </div>
                        <div class="text-end">
                            <h4 class="mb-1">Vendas Totais</h4>
                            <h2 class="mb-0 fw-bold text-primary">MZN
                                <?php echo number_format($total_sales, 2, ',', '.'); ?></h2>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="ti-arrow-up text-success me-1"></i>
                        <p class="mb-0 text-success">Atualizado agora</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Orders Card -->
        <div class="col-sm-6 col-lg-3 grid-margin stretch-card">
            <div class="card card-rounded border-start border-success border-4">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="circle-progress-width">
                            <i class="ti-shopping-cart icon-md text-success"></i>
                        </div>
                        <div class="text-end">
                            <h4 class="mb-1">Total Pedidos</h4>
                            <h2 class="mb-0 fw-bold text-success"><?php echo number_format($total_orders); ?></h2>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="ti-check-box text-success me-1"></i>
                        <p class="mb-0">Pedidos processados</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Average Ticket Card -->
        <div class="col-sm-6 col-lg-3 grid-margin stretch-card">
            <div class="card card-rounded border-start border-warning border-4">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="circle-progress-width">
                            <i class="ti-receipt icon-md text-warning"></i>
                        </div>
                        <div class="text-end">
                            <h4 class="mb-1">Ticket Médio</h4>
                            <h2 class="mb-0 fw-bold text-warning">
                                MZN
                                <?php echo ($total_orders > 0) ? number_format($total_sales/$total_orders, 2, ',', '.') : '0,00'; ?>
                            </h2>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="ti-stats-up text-warning me-1"></i>
                        <p class="mb-0">Por pedido</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Orders -->
        <div class="col-sm-6 col-lg-3 grid-margin stretch-card">
            <div class="card card-rounded border-start border-info border-4">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="circle-progress-width">
                            <i class="ti-calendar icon-md text-info"></i>
                        </div>
                        <div class="text-end">
                            <h4 class="mb-1">Pedidos Hoje</h4>
                            <h2 class="mb-0 fw-bold text-info"><?php echo get_today_orders(); ?></h2>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="ti-time text-info me-1"></i>
                        <p class="mb-0">Nas últimas 24h</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row">
        <!-- Monthly Sales Chart -->
        <div class="col-lg-8 grid-margin stretch-card">
            <div class="card card-rounded">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title mb-0">
                            <i class="ti-bar-chart-alt text-primary me-2"></i>
                            Análise de Vendas Mensais
                        </h4>
                        <div class="dropdown">
                            <button class="btn btn-link p-0" type="button" id="monthlyOptions"
                                data-bs-toggle="dropdown">
                                <i class="ti-more-alt"></i>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="monthlyOptions"
                                style="color:#000; background-color:orange; z-index:199;">
                                <li><a class="dropdown-item" href="#">Exportar PDF</a></li>
                                <li><a class="dropdown-item" href="#">Exportar Excel</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="chart-container" style="position: relative; height: 400px;">
                        <canvas id="monthlySalesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Weekly Trend Chart -->
        <div class="col-lg-4 grid-margin stretch-card">
            <div class="card card-rounded">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title mb-0">
                            <i class="ti-stats-up text-success me-2"></i>
                            Tendência Semanal
                        </h4>
                        <div class="dropdown">
                            <button class="btn btn-link p-0" type="button" id="weeklyOptions" data-bs-toggle="dropdown">
                                <i class="ti-more-alt"></i>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="weeklyOptions"
                                style="color:#000; background-color:orange; z-index:199;">
                                <li><a class="dropdown-item" href="#">Últimos 7 dias</a></li>
                                <li><a class="dropdown-item" href="#">Últimos 30 dias</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="chart-container" style="position: relative; height: 400px;">
                        <!-- Para o gráfico semanal -->
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders Table -->
    <div class="row">
        <div class="col-12 grid-margin">
            <div class="card card-rounded">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title mb-0">
                            <i class="ti-time text-info me-2"></i>
                            Pedidos Recentes
                        </h4>
                        <div>
                            <button type="button" class="btn btn-primary btn-sm">
                                <i class="ti-plus me-1"></i> Novo Pedido
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Mesa</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Data</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $recent_orders = get_recent_orders(10);
                                    foreach ($recent_orders as $order):
                                        $status_classes = [
                                            'active' => 'bg-warning',
                                            'completed' => 'bg-success',
                                            'paid' => 'bg-info',
                                            'canceled' => 'bg-danger'
                                        ];
                                        $status_class = $status_classes[$order['status']] ?? 'bg-secondary';
                                    ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="ti-file text-primary me-2"></i>
                                            #<?php echo $order['id']; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="ti-layout-grid2 text-success me-2"></i>
                                            Mesa <?php echo $order['table_id']; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold">
                                            MZN <?php echo number_format($order['total_amount'], 2, ',', '.'); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $status_class; ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="ti-calendar text-muted me-2"></i>
                                            <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="view_order.php?id=<?php echo $order['id']; ?>"
                                                class="btn btn-outline-primary btn-sm">
                                                <i class="ti-eye"></i>
                                            </a>
                                            <a href="print_order.php?id=<?php echo $order['id']; ?>"
                                                class="btn btn-outline-success btn-sm">
                                                <i class="ti-printer"></i>
                                            </a>
                                        </div>
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

    <!-- Hidden inputs for chart data -->
    <input type="hidden" id="monthlySalesData" value='<?php echo $monthly_sales_json; ?>'>
    <input type="hidden" id="weeklySalesData" value='<?php echo $weekly_sales_json; ?>'>

  
    <script src="assets/jquery-3.7.1.min.js"></script>
    <script src="assets/jquery.dataTables.min.js"></script>
    <script src="assets/dataTables.bootstrap5.min.js"></script>
    <script>
    // reports.js
    document.addEventListener('DOMContentLoaded', function() {
        // Configurações globais do Chart.js
        Chart.defaults.color = '#6c7293';
        Chart.defaults.font.family = "'Roboto', sans-serif";

        // Função para formatar valores monetários
        const formatMoney = (value) => {
            return new Intl.NumberFormat('pt-MZ', {
                style: 'currency',
                currency: 'MZN'
            }).format(value);
        };

        // Configuração do gráfico de vendas mensais
        const initMonthlySalesChart = () => {
            const ctx = document.getElementById('monthlySalesChart');
            if (!ctx) return;

            try {
                const monthlySalesData = JSON.parse(document.getElementById('monthlySalesData').value);

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: Object.keys(monthlySalesData),
                        datasets: [{
                            label: 'Vendas Mensais',
                            data: Object.values(monthlySalesData),
                            backgroundColor: 'rgba(0, 147, 183, 0.2)',
                            borderColor: '#0093b7',
                            borderWidth: 2,
                            borderRadius: 5
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: (context) => 'Vendas: ' + formatMoney(context.raw)
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: (value) => formatMoney(value)
                                }
                            }
                        }
                    }
                });
            } catch (error) {
                console.error('Erro ao inicializar gráfico mensal:', error);
            }
        };

        // Configuração do gráfico de vendas semanais
        const initWeeklySalesChart = () => {
            const ctx = document.getElementById('salesChart');
            if (!ctx) return;

            try {
                const weeklySalesData = JSON.parse(document.getElementById('weeklySalesData').value);

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: Object.keys(weeklySalesData),
                        datasets: [{
                            label: 'Vendas Semanais',
                            data: Object.values(weeklySalesData),
                            borderColor: '#7978e9',
                            backgroundColor: 'rgba(121, 120, 233, 0.2)',
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: (context) => 'Vendas: ' + formatMoney(context.raw)
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: (value) => formatMoney(value)
                                }
                            }
                        }
                    }
                });
            } catch (error) {
                console.error('Erro ao inicializar gráfico semanal:', error);
            }
        };

        // Configuração da tabela de pedidos recentes
        const initRecentOrdersTable = () => {
            const table = document.querySelector('.table');
            if (!table) return;

            // Inicializar DataTable
            $(table).DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json'
                },
                pageLength: 5,
                lengthChange: false,
                searching: false,
                ordering: true,
                info: false,
                responsive: true
            });
        };

        // Inicializar todos os componentes
        initMonthlySalesChart();
        initWeeklySalesChart();
        initRecentOrdersTable();
    });
    </script>
    <?php include '../includes/footer.php'; ?>