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

// Calcular o troco
$total_paid = $sale['cash_amount'] + $sale['card_amount'] + $sale['mpesa_amount'] + $sale['emola_amount'];
$change = max(0, $total_paid - $sale['total_amount']);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo da Venda #<?php echo $sale_id; ?> - Lu & Yosh Catering</title>
    <style>
    body {
        font-family: 'Arial', sans-serif;
        font-size: 12px;
        line-height: 1.6;
        color: #333;
        margin: 0;
        padding: 20px;
    }

    .receipt {
        width: 80mm;
        margin: 0 auto;
        padding: 10px;
        border: 1px solid #ddd;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .header {
        text-align: center;
        margin-bottom: 20px;
    }

    .logo {
        max-width: 80px;
        height: auto;
        margin-bottom: 10px;
    }

    h2,
    h3 {
        margin: 5px 0;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 15px;
    }

    th,
    td {
        border-bottom: 1px solid #ddd;
        padding: 8px 4px;
        text-align: left;
    }

    th {
        background-color: #f8f8f8;
    }

    .total {
        font-weight: bold;
        background-color: #f0f0f0;
    }

    .payment-methods {
        margin-bottom: 15px;
    }

    .footer {
        margin-top: 20px;
        text-align: center;
        font-size: 10px;
        color: #666;
    }

    @media print {
        body {
            width: 80mm;
            margin: 0;
            padding: 0;
        }

        .receipt {
            border: none;
            box-shadow: none;
        }
    }
    </style>
</head>

<body>
    <div class="receipt">
        <div class="header">
            <img src="../public/assets/images/logo.png" alt="Lu & Yosh Catering Logo" class="logo">
            <h2>Lu & Yosh Catering</h2>
            <p>Av. Eduardo Mondlane, 1234<br>
                Quelimane, Moçambique<br>
                Tel: +258 21 123 456<br>
                NUIT: 123456789</p>
            <h3>Recibo da Venda #<?php echo $sale_id; ?></h3>
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
                    <td><?php echo number_format($item['unit_price'], 2); ?></td>
                    <td><?php echo number_format($item['quantity'] * $item['unit_price'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="total">
                    <td colspan="3">Total</td>
                    <td><?php echo number_format($sale['total_amount'], 2); ?></td>
                </tr>
            </tfoot>
        </table>
        <div class="payment-methods">
            <h4>Métodos de Pagamento:</h4>
            <table>
                <?php if ($sale['cash_amount'] > 0): ?>
                <tr>
                    <td>Dinheiro:</td>
                    <td><?php echo number_format($sale['cash_amount'], 2); ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($sale['card_amount'] > 0): ?>
                <tr>
                    <td>Cartão:</td>
                    <td><?php echo number_format($sale['card_amount'], 2); ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($sale['mpesa_amount'] > 0): ?>
                <tr>
                    <td>M-Pesa:</td>
                    <td><?php echo number_format($sale['mpesa_amount'], 2); ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($sale['emola_amount'] > 0): ?>
                <tr>
                    <td>Emola:</td>
                    <td><?php echo number_format($sale['emola_amount'], 2); ?></td>
                </tr>
                <?php endif; ?>
                <tr class="total">
                    <td>Total Pago:</td>
                    <td><?php echo number_format($total_paid, 2); ?></td>
                </tr>
                <?php if ($change > 0): ?>
                <tr>
                    <td>Troco:</td>
                    <td><?php echo number_format($change, 2); ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
        <div class="footer">
            <p>Obrigado pela sua preferência!</p>
            <p>Este documento não serve como fatura</p>
        </div>
    </div>
    <script>
        window.onload = function() {
        window.print();
        setTimeout(function() {
            window.close();
        }, 2000);
    }
    window.onafterprint = function() {
        window.close();
    }
    window.onbeforeprint = function() {
        window.print();
    }

    window.onbeforeunload = function() {
        window.close();
    }

    window.onafterprint = function() {
        // A janela será fechada automaticamente após 2 segundos
    }
    </script>
</body>

</html>