<?php
require_once '../config/config.php';
require_login();

$pageTitle = "Detalhes da Venda";

if (!isset($_GET['id'])) {
    die('ID da venda não fornecido');
}

$sale_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
$sale = get_sale($sale_id);
$sale_items = get_sale_items($sale_id);

if (!$sale) {
    die('Venda não encontrada');
}

include '../includes/header.php';
?>

<div class="container-fluid">
    <h1 class="mt-4">Detalhes da Venda #<?php echo $sale_id; ?></h1>
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Informações da Venda</h5>
            <p><strong>Data:</strong> <?php echo date('d/m/Y H:i', strtotime($sale['sale_date'])); ?></p>
            <p><strong>Total:</strong> MZN <?php echo number_format($sale['total_amount'], 2); ?></p>
            <p><strong>Método de Pagamento:</strong> <?php echo ucfirst($sale['payment_method']); ?></p>
            <p><strong>Status:</strong> <?php echo ucfirst($sale['status']); ?></p>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Itens da Venda</h5>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Quantidade</th>
                            <th>Preço Unitário</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sale_items as $item): ?>
                        <tr>
                            <td><?php echo get_product_name($item['product_id']); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td>MZN <?php echo number_format($item['unit_price'], 2); ?></td>
                            <td>MZN <?php echo number_format($item['quantity'] * $item['unit_price'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3">Total</th>
                            <th>MZN <?php echo number_format($sale['total_amount'], 2); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="mb-4">
        <a href="sales.php" class="btn btn-secondary">Voltar para Vendas</a>
        <button class="btn btn-primary" onclick="printReceipt(<?php echo $sale_id; ?>)">Imprimir Recibo</button>
    </div>
</div>

<script>
function printReceipt(saleId) {
    window.open('print_receipt.php?id=' + saleId, '_blank');
}
</script>

<?php include '../includes/footer.php'; ?>