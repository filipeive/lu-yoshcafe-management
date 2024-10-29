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
    <title>Recibo da Venda #<?php echo $sale_id; ?></title>
    <style>
    @page {
        size: 58mm auto;
        margin: 0;
    }

    body {
        font-family: 'Courier New', monospace;
        font-size: 12px;
        width: 58mm;
        margin: 0 auto;
        padding: 5px;
        color: #000;
    }

    .header,
    .footer {
        text-align: center;
        margin-bottom: 5px;
    }

    .header img {
        max-width: 120px;
    }

    .divider {
        border-top: 1px dashed #000;
        margin: 8px 0;
    }

    .item {
        display: flex;
        justify-content: space-between;
        margin: 3px 0;
    }

    .total {
        font-weight: bold;
        margin-top: 10px;
    }

    .footer {
        font-size: 10px;
        font-weight: bold;
        margin-top: 20px;
    }

    @media print {
        .no-print {
            display: none;
        }
    }
    .button-container {
            text-align: center;
            margin-top: 20px;
        }
        .print-button, .close-button {
            padding: 8px 16px;
            margin: 0 5px;
            cursor: pointer;
            border: none;
            border-radius: 4px;
        }
        .print-button {
            background-color: #4CAF50;
            color: white;
        }
        .close-button {
            background-color: #f44336;
            color: white;
        }
    </style>
</head>

<body>
    <div class="header">
        <img src="../public/assets/images/Logo.png" alt="Lu & Yosh Catering Logo">
        <h2>Lu & Yosh Catering</h2>
        <p>Av. Eduardo Mondlane, 1234<br>Quelimane, Moçambique<br>Tel: +258 21 123 456<br>NUIT: 123456789</p>
        <h3>Recibo da Venda #<?php echo $sale_id; ?></h3>
        <p>Data: <?php echo date('d/m/Y H:i', strtotime($sale['sale_date'])); ?></p>
    </div>

    <div class="divider"></div>

    <?php foreach ($sale_items as $item): ?>
    <div class="item">
        <span><?php echo htmlspecialchars(get_product_name($item['product_id'])); ?>
            x<?php echo $item['quantity']; ?></span>
        <span><?php echo number_format($item['quantity'] * $item['unit_price'], 2); ?> MT</span>
    </div>
    <?php endforeach; ?>

    <div class="divider"></div>

    <div class="total">
        <div class="item">
            <span>Total:</span>
            <span><?php echo number_format($sale['total_amount'], 2); ?> MT</span>
        </div>
    </div>

    <div class="divider"></div>

    <div class="payment-methods">
        <h4>Métodos de Pagamento:</h4>
        <?php if ($sale['cash_amount'] > 0): ?><p>Dinheiro: <?php echo number_format($sale['cash_amount'], 2); ?> MT</p>
        <?php endif; ?>
        <?php if ($sale['card_amount'] > 0): ?><p>Cartão: <?php echo number_format($sale['card_amount'], 2); ?> MT</p>
        <?php endif; ?>
        <?php if ($sale['mpesa_amount'] > 0): ?><p>M-Pesa: <?php echo number_format($sale['mpesa_amount'], 2); ?> MT</p>
        <?php endif; ?>
        <?php if ($sale['emola_amount'] > 0): ?><p>Emola: <?php echo number_format($sale['emola_amount'], 2); ?> MT</p>
        <?php endif; ?>
        <p>Total Pago: <?php echo number_format($total_paid, 2); ?> MT</p>
        <?php if ($change > 0): ?><p>Troco: <?php echo number_format($change, 2); ?> MT</p><?php endif; ?>
    </div>

    <div class="footer">
        <p>Obrigado pela sua preferência!</p>
        <p>Este documento não serve como fatura</p>
        <p>Impresso em: <?php echo date('d/m/Y H:i:s'); ?></p>
    </div>
    <div class="no-print button-container">
        <button class="print-button" onclick="printAndClose()">
            <i class="mdi mdi-printer"></i> Imprimir
        </button>
        <button class="close-button" onclick="closeAndReturn()">
            <i class="mdi mdi-close"></i> Fechar
        </button>
    </div>
    <script>
    function imprimirRecibo() {
        window.print();
        // Aguarda 2 segundos e redireciona para a página anterior
        setTimeout(function() {
            window.history.back();
        }, 2000);
    }

     // Função para fechar a janela e redirecionar para pedidos
     function closeAndReturn() {
        window.location.href = "sales.php";
    }

    // Imprime automaticamente ao carregar a página
    window.onload = function() {
        window.print();

        // Simula um clique para fechar e redirecionar após 2 segundos
        setTimeout(closeAndReturn, 500);
    };
    </script>

</body>

</html>