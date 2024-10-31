<?php
require_once '../config/config.php';
require_login();

if (!isset($_GET['id'])) {
    die("ID do pedido não especificado.");
}

$order_id = (int)$_GET['id'];

// Obter detalhes do pedido
$stmt = $pdo->prepare("
    SELECT o.*, t.number as table_number 
    FROM orders o 
    LEFT JOIN tables t ON o.table_id = t.id 
    WHERE o.id = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    die("Pedido não encontrado.");
}

// Obter itens do pedido
$stmt = $pdo->prepare("
    SELECT oi.*, p.name as product_name, p.price as unit_price 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();

// Informações do restaurante
$company_name = "Lu & Yosh Catering";
$company_address = "Av. Eduardo Mondlane, 1234, Quelimane, Moçambique";
$company_phone = "+258 21 123 456";
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <title>Recibo #<?php echo str_pad($order_id, 5, '0', STR_PAD_LEFT); ?></title>
    <style>
    @page {
        size: 58mm 297mm;
        margin: 0;
    }

    @media print {
        .no-print {
            display: none;
        }
    }

    body {
        font-family: 'Arial';
        font-size: 12px;
        width: 58mm 297mm;
        margin: auto;
        padding: 5px;
        color: #000;
    }

    .header {
        text-align: center;
        margin-bottom: 10px;
    }

    .divider {
        border-top: 1px dashed #000;
        margin: 5px 0;
    }

    .items {
        display: flex;
        flex-direction: column;
        /* Mudado para coluna */
        align-items: space-between;
        /* Alinha os itens ao centro */
        margin-top: 10px;
        width: 80%;
    }

    .item {
        display: flex;
        justify-content: space-between;
        width: 80%;
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
        text-align: center;
        margin-top: 20px;
        font-size: 10px;
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
        <h2><?php echo htmlspecialchars($company_name); ?></h2>
        <p><?php echo htmlspecialchars($company_address); ?></p>
        <p><?php echo htmlspecialchars($company_phone); ?></p>
    </div>

    <div class="divider"></div>

    <div>
        <p>Recibo #<?php echo str_pad($order_id, 5, '0', STR_PAD_LEFT); ?></p>
        <p>Data: <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
        <p>Mesa: <?php echo isset($order['table_number']) ? htmlspecialchars($order['table_number']) : 'N/A'; ?></p>
    </div>

    <div class="divider"></div>

    <div class="items">
        <?php foreach ($items as $item): ?>
        <div class="item">
            <span><?php echo $item['quantity']; ?>x <?php echo htmlspecialchars($item['product_name']); ?></span>
            <span>MZN <?php echo number_format($item['quantity'] * $item['unit_price'], 2); ?></span>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="divider"></div>

    <div class="total">
        <span>Total:</span>
        <span>MZN <?php echo number_format($order['total_amount'], 2); ?></span>
    </div>

    <div class="footer">
        <p>Obrigado pela preferência!</p>
        <p><?php echo date('d/m/Y H:i'); ?></p>
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
    // Função para fechar a janela e redirecionar para pedidos
    function closeAndReturn() {
        window.location.href = "orders.php";
    }

    function closeTab() {
        window.close(); // Fecha a aba do navegador
    }

    // Imprime automaticamente ao carregar a página
    window.onload = function() {
        printAndClose();
    };
    // Imprime automaticamente ao carregar a página
    window.onload = function() {
        window.print();
        // Simula um clique para fechar e redirecionar após 2 segundos
        setTimeout(closeAndReturn, 1000);
    };
    </script>

</body>

</html>