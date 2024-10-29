<?php
require_once '../config/config.php';
require_login();

$total_sales_today = get_total_sales_today();
$open_orders = order_get_open_count();
$low_stock_products = get_low_stock_products();
$tables = get_all_tables();
/// Certifique-se de que o $sales_data está definido e não vazio
$sales_data = get_hourly_sales_data();

// Formate os dados para o gráfico
$formatted_sales_data = [];
foreach ($sales_data as $data) {
    $formatted_sales_data[] = [
        'hour' => $data['hour'],
        'value' => (float)$data['total_amount']
    ];
}
// Passando os dados formatados para o gráfico em formato JSON
$json_sales_data = json_encode($formatted_sales_data);


include '../includes/header.php';
?>

<!-- Estilos CSS modernos -->
<style>
:root {
    --primary-color: #4f46e5;
    --success-color: #22c55e;
    --danger-color: #ef4444;
    --warning-color: #f59e0b;
}

.dashboard-container {
    padding: 1.5rem;
    background-color: rgba(255, 255, 255, 0.2);
    /*background: linear-gradient(to bottom right, #f8fafc, #f1f5f9);*/
    min-height: 100vh;
    border-radius: 10px;
}

.stats-card {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.icon-container {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
}

.quick-action {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    text-align: center;
    transition: all 0.3s ease;
    border: 1px solid #e5e7eb;
    cursor: pointer;
}

.quick-action:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.quick-action .icon {
    transition: transform 0.3s ease;
}

.quick-action:hover .icon {
    transform: scale(1.1);
}

.table-status {
    border-radius: 0.75rem;
    padding: 1rem;
    transition: all 0.3s ease;
    color: #fff !important;
}

.table-status:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.chart-container {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    margin-top: 1.5rem;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fadeIn 0.5s ease forwards;
}

.header-title {
    font-size: 1.875rem;
    font-weight: bold;
    color: #1f2937;
    margin-bottom: 2rem;
}

/* Gradientes para os cards */
.gradient-primary {
    background: linear-gradient(135deg, #4f46e5, #6366f1);
}

.gradient-success {
    background: linear-gradient(135deg, #22c55e, #16a34a);
}

.gradient-danger {
    background: linear-gradient(135deg, #ef4444, #dc2626);
}

.gradient-warning {
    background: linear-gradient(135deg, #f59e0b, #d97706);
}
</style>

<!-- Conteúdo do Dashboard -->
<div class="dashboard-container">
    <!-- Cabeçalho -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="header-title animate-fade-in text-white">Dashboard Lu & Yoshi Catering</h1>
        <button class="btn btn-primary" onclick="location.href='pos.php'">
            <i class="mdi mdi-cash-register me-2"></i>
            Abrir PDV
        </button>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="row g-4 mb-4">
        <div class="col-md-3 animate-fade-in" style="animation-delay: 0.1s;">
            <div class="stats-card gradient-primary text-white">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="icon-container bg-white bg-opacity-25">
                            <i class="mdi mdi-cash-multiple mdi-24px text-white"></i>
                        </div>
                        <h6 class="card-title mb-2">Vendas Hoje</h6>
                        <h3 class="mb-0">MZN <?php echo number_format($total_sales_today, 2, ',', '.'); ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 animate-fade-in" style="animation-delay: 0.2s;">
            <div class="stats-card gradient-success text-white">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="icon-container bg-white bg-opacity-25">
                            <i class="mdi mdi-food-fork-drink mdi-24px text-white"></i>
                        </div>
                        <h6 class="card-title mb-2">Pedidos Abertos</h6>
                        <h3 class="mb-0"><?php echo $open_orders; ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 animate-fade-in" style="animation-delay: 0.3s;">
            <div class="stats-card gradient-danger text-white">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="icon-container bg-white bg-opacity-25">
                            <i class="mdi mdi-alert-circle mdi-24px text-white"></i>
                        </div>
                        <h6 class="card-title mb-2">Estoque Baixo</h6>
                        <h3 class="mb-0"><?php echo $low_stock_products; ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 animate-fade-in" style="animation-delay: 0.4s;">
            <div class="stats-card gradient-warning text-white">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="icon-container bg-white bg-opacity-25">
                            <i class="mdi mdi-table-furniture mdi-24px text-white"></i>
                        </div>
                        <h6 class="card-title mb-2">Total de Mesas</h6>
                        <h3 class="mb-0"><?php echo count($tables); ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ações Rápidas -->
    <div class="card mb-4 animate-fade-in" style="animation-delay: 0.5s;">
        <div class="card-body">
            <h4 class="card-title mb-4">
                <i class="mdi mdi-lightning-bolt text-warning me-2"></i>
                Ações Rápidas
            </h4>
            <div class="row g-4">
                <?php
                $quick_actions = [
                    [
                        'icon' => 'mdi-table-large',
                        'title' => 'Gerenciar Mesas',
                        'desc' => 'Controle de mesas',
                        'url' => 'tables.php',
                        'color' => 'primary'
                    ],
                    [
                        'icon' => 'mdi-cart',
                        'title' => 'Pedidos',
                        'desc' => 'Gerir Pedido',
                        'url' => 'orders.php',
                        'color' => 'success'
                    ],
                    [
                        'icon' => 'mdi-chart-bar',
                        'title' => 'Relatórios',
                        'desc' => 'Análise de dados',
                        'url' => 'reports.php',
                        'color' => 'info'
                    ],
                    [
                        'icon' => 'mdi-cash-register',
                        'title' => 'Venda Rápida',
                        'desc' => 'PDV rápido',
                        'url' => 'pos.php',
                        'color' => 'warning'
                    ]
                ];

                foreach ($quick_actions as $action):
                ?>
                <div class="col-md-3">
                    <a href="<?php echo $action['url']; ?>" class="text-decoration-none">
                        <div class="quick-action">
                            <div class="icon-container bg-<?php echo $action['color']; ?> bg-opacity-10 mx-auto mb-3">
                                <i
                                    class="mdi <?php echo $action['icon']; ?> mdi-24px text-<?php echo $action['color']; ?> icon"></i>
                            </div>
                            <h5 class="font-weight-bold"><?php echo $action['title']; ?></h5>
                            <p class="text-muted mb-0"><?php echo $action['desc']; ?></p>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Gráfico de Vendas -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Vendas Diárias</h4>
                    <div class="chart-container" style="position: relative; height:300px;">
                        <canvas id="dailySalesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <!-- Status das Mesas -->
        <div class="col-md-6 animate-fade-in" style="animation-delay: 0.7s;">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">Status das Mesas</h4>
                    <div class="row g-3">
                        <?php foreach ($tables as $table): ?>
                        <div class="col-6" style="color:white;">
                            <div
                                class="table-status <?php echo ($table['status'] === 'occupied') ? 'bg-danger bg-opacity-10' : 'bg-success bg-opacity-10'; ?>">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6
                                            class="mb-1 text-white <?php echo ($table['status'] === 'occupied') ? 'text-danger' : 'text-success'; ?>">
                                            Mesa <?php echo $table['number']; ?>
                                        </h6>
                                        <small
                                            class="<?php echo ($table['status'] === 'occupied') ? 'text-danger' : 'text-success'; ?>">
                                            <?php echo ($table['status'] === 'occupied') ? 'Ocupada' : 'Livre'; ?>
                                        </small>
                                    </div>
                                    <a href="tables.php?id=<?php echo $table['id']; ?>"
                                        class="btn btn-sm <?php echo ($table['status'] === 'occupied') ? 'btn-outline-danger' : 'btn-outline-success'; ?>">
                                        Gerenciar
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts para o gráfico -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Dados para o gráfico diário (vendas por hora)
    const dailySalesLabels = <?php echo json_encode(array_column($sales_data, 'hour')); ?>;
    const dailySalesData = <?php echo json_encode(array_column($sales_data, 'value')); ?>;

    // Configuração do gráfico de vendas diárias
    const ctxDaily = document.getElementById('dailySalesChart').getContext('2d');
    new Chart(ctxDaily, {
        type: 'bar',
        data: {
            labels: dailySalesLabels,
            datasets: [{
                label: 'Vendas (MZN)',
                data: dailySalesData,
                borderColor: '#4f46e5',
                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        display: true,
                        color: 'rgba(0,0,0,0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>