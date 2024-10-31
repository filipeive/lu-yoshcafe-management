<?php
require_once '../config/config.php';
require_login();

if (!isset($_GET['id'])) {
    $_SESSION['error_message'] = "ID da venda não fornecido";
    header('Location: sales.php');
    exit;
}

$sale_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
$sale = get_sale($sale_id);
$sale_items = get_sale_items($sale_id);

if (!$sale) {
    $_SESSION['error_message'] = "Venda não encontrada";
    header('Location: sales.php');
    exit;
}

// Função helper para o status da venda
function getSaleStatusBadge($status) {
    switch (strtolower($status)) {
        case 'completed':
        case 'concluída':
            return '<span class="badge bg-success"><i class="mdi mdi-check-circle me-1"></i>Concluída</span>';
        case 'pending':
        case 'pendente':
            return '<span class="badge bg-warning"><i class="mdi mdi-clock-outline me-1"></i>Pendente</span>';
        case 'cancelled':
        case 'cancelada':
            return '<span class="badge bg-danger"><i class="mdi mdi-close-circle me-1"></i>Cancelada</span>';
        default:
            return '<span class="badge bg-secondary"><i class="mdi mdi-help-circle me-1"></i>' . ucfirst($status) . '</span>';
    }
}

// Função helper para o método de pagamento
function getPaymentMethodBadge($method) {
    $icons = [
        'cash' => 'mdi-cash',
        'dinheiro' => 'mdi-cash',
        'card' => 'mdi-credit-card',
        'cartão' => 'mdi-credit-card',
        'transfer' => 'mdi-bank-transfer',
        'transferência' => 'mdi-bank-transfer',
        'pix' => 'mdi-qrcode',
    ];
    
    $method = strtolower($method);
    $icon = isset($icons[$method]) ? $icons[$method] : 'mdi-cash';
    
    return '<span class="badge bg-info"><i class="mdi ' . $icon . ' me-1"></i>' . ucfirst($method) . '</span>';
}

include '../includes/header.php';
?>


<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="card-title mb-0">
                            <i class="mdi mdi-receipt me-2"></i>
                            Venda #<?php echo $sale_id; ?>
                        </h4>
                        <small class="text-muted">
                            Realizada em <?php echo date('d/m/Y \à\s H:i', strtotime($sale['sale_date'])); ?>
                        </small>
                    </div>
                    <div>
                        <?php echo getSaleStatusBadge($sale['status']); ?>
                    </div>
                </div>

                <div class="row">
                    <!-- Sumário da Venda -->
                    <div class="col-md-12 mb-4">
                        <div class="card border-0 bg-primary text-white">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="d-flex flex-column">
                                            <small>Total da Venda</small>
                                            <h3 class="mb-0">MZN
                                                <?php echo number_format($sale['total_amount'], 2, ',', '.'); ?></h3>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="d-flex flex-column">
                                            <small>Itens Vendidos</small>
                                            <h3 class="mb-0"><?php echo count($sale_items); ?></h3>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="d-flex flex-column">
                                            <small>Método de Pagamento</small>
                                            <h3 class="mb-0">
                                                <?php echo getPaymentMethodBadge($sale['payment_method']); ?></h3>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="d-flex flex-column">
                                            <small>Média por Item</small>
                                            <h3 class="mb-0">MZN
                                                <?php echo number_format($sale['total_amount'] / count($sale_items), 2, ',', '.'); ?>
                                            </h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Itens da Venda -->
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title mb-4">
                                    <i class="mdi mdi-basket me-2"></i>
                                    Itens da Venda
                                </h5>
                                <div class="table-responsive">
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
                                            <?php foreach ($sale_items as $item): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="mdi mdi-package-variant me-2 text-primary"></i>
                                                        <?php echo get_product_name($item['product_id']); ?>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-info"><?php echo $item['quantity']; ?></span>
                                                </td>
                                                <td class="text-end">
                                                    MZN <?php echo number_format($item['unit_price'], 2, ',', '.'); ?>
                                                </td>
                                                <td class="text-end">
                                                    <strong>MZN
                                                        <?php echo number_format($item['quantity'] * $item['unit_price'], 2, ',', '.'); ?></strong>
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
                                                        <?php echo number_format($sale['total_amount'], 2, ',', '.'); ?>
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

                <!-- Ações -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <a href="sales.php" class="btn btn-outline-secondary">
                                        <i class="mdi mdi-arrow-left me-2"></i>
                                        Voltar para Vendas
                                    </a>
                                    <div>
                                        <button class="btn btn-primary" onclick="printReceipt(<?php echo $sale_id; ?>)">
                                            <i class="mdi mdi-printer me-2"></i>
                                            Imprimir Recibo
                                        </button>
                                        <?php if ($sale['status'] !== 'cancelled'): ?>
                                        <button class="btn btn-outline-danger ms-2"
                                            onclick="exportPDF(<?php echo $sale_id; ?>)">
                                            <i class="mdi mdi-file-pdf me-2"></i>
                                            Exportar PDF
                                        </button>
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


<script>
function printReceipt(saleId) {
    window.open('print_receipt.php?id=' + saleId, '_blank');
}

function exportPDF(saleId) {
    window.open('export_sale_pdf.php?id=' + saleId, '_blank');
}

// Tooltip initialization
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<?php include '../includes/footer.php'; ?>