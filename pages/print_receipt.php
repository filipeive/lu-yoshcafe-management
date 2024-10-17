<?php
require_once '../config/config.php';
if (!isset($_GET['id'])) {
    die('ID da venda não fornecido');
}
$sale_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
$sale = get_sale($sale_id);
$sale_items = get_sale_items($sale_id);
if (!$sale) {
    die('Venda não encontrada');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo da Venda #<?php echo $sale_id; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .receipt {
            width: 300px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th,
        td {
            border-bottom: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .total {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <h2>Recibo da Venda #<?php echo $sale_id; ?></h2>
            <p>Data: <?php echo date('d/m/Y H:i', strtotime($sale['sale_date'])); ?></p>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Qtd</th>
                    <th>Preço</th>
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
                <tr class="total">
                    <td colspan="3">Total</td>
                    <td>MZN <?php echo number_format($sale['total_amount'], 2); ?></td>
                </tr>
            </tfoot>
        </table>
        <p>Método de Pagamento: <?php echo $sale['payment_method']; ?></p>
        <p>Obrigado pela sua compra!</p>
    </div>
    <script>
        window.onload = function() {
            window.print();
            // Set a timeout to close the window after 10 seconds
            setTimeout(function() {
                window.close();
            }, 2000);
        }

        window.onbeforeprint = function() {
            window.print();
        }

        window.onbeforeunload = function() {
            window.close();
        }

        window.onafterprint = function() {
            // The window will now close automatically after 10 seconds,
            // so we don't need to close it immediately after printing
        }
    </script>
</body>
</html>