<?php
require_once '../config/config.php';

// Verifica se o ID da venda foi fornecido
if (!isset($_GET['id'])) {
    die('ID da venda não fornecido');
}

$sale_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
$sale = get_sale($sale_id);
$sale_items = get_sale_items($sale_id);

// Verifica se a venda existe
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
        size: 56mm auto;
        margin: 0;
    }

    body {
        font-family: 'Arial';
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
        max-width: 100px;
        margin-bottom: -10px;
    }

    .divider {
        border-top: 1px dashed #000;
        margin: 8px 0;
    }

    .items {
        display: flex;
        flex-direction: column;
        /* Mudado para coluna */
        align-items: center;
        /* Alinha os itens ao centro */
        margin-top: 10px;
        width: 100%;
    }

    .item {
        display: flex;
        justify-content: space-between;
        /* Ajustado para espaçar corretamente */
        width: 80%;
        /* Toma toda a largura disponível */
        margin-bottom: 5px;
        font-size: 12px;
    }

    .total {
        align-items: center;
        margin-right: 5px;
        font-weight: bold;
        margin-top: 5px;
        font-size: 11px;
    }

    .footer {
        font-size: 10px;
        font-weight: bold;
        margin-top: 10px;
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

    .print-button,
    .close-button {
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

    <div class="items">
        <?php foreach ($sale_items as $item): ?>
        <div class="item">
            <span><?php echo htmlspecialchars(get_product_name($item['product_id'])); ?>
                x<?php echo $item['quantity']; ?></span>
            <span><?php echo number_format($item['quantity'] * $item['unit_price'], 2); ?> </span>
        </div>
        <?php endforeach; ?>
        <div style="font-weight: bold; border-top: 1px dashed #000; width:100%"></div>
        <div class="item" style="font-weight: bold;">
            <span>Total:</span>
            <span><?php echo number_format($sale['total_amount'], 2); ?> </span>
        </div>
    </div>
    <div class="divider"></div>
    <div class="footer">
        <p>Obrigado pela sua preferência!</p>
        <p>Este documento não serve como fatura</p>
        <p>Impresso em: <?php echo date('d/m/Y H:i:s'); ?></p>
    </div>

    <div class="no-print button-container">
        <button class="print-button" onclick="printReceipt()">
            <i class="mdi mdi-printer"></i> Imprimir
        </button>
        <button class="close-button" onclick="closeTab()">
            <i class="mdi mdi-close"></i> Fechar
        </button>
    </div>

    <script>
    function printReceipt() {
        window.print();
        setTimeout(closeTab, 2000); // Aguarda 2 segundos e então fecha a aba
    }

    function closeTab() {
        window.close(); // Fecha a aba do navegador
    }

    // Imprime automaticamente ao carregar a página
    window.onload = function() {
        printReceipt();
    };
    </script>

</body>

</html>