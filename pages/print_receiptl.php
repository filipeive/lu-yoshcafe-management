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

<title>Recibo da Venda #<?php echo $sale_id; ?></title>
<style>
@page {
    size: 58mm auto;
    margin: 0;
}

body {
    font-family: Arial, monospace;
    font-size: 12px;
    width: 54mm;
    /* Ajuste para caber na página */
    margin: 0 auto;
    padding: 5px;
    color: #000;
}

.receipt-container {
    display: flex;
    margin-bottom: -10px;
    flex-direction: column;
    align-items: center;
    width: 100%;
}

.header,
.footer {
    text-align: center;
    margin-bottom: 5px;
    width: 100%;
}

.header img {
    max-width: 100px;
    margin-bottom: -10px;
}

.divider {
    border-top: 1px dashed #000;
    margin: 8px 0;
    width: 100%;
}

.items,
.totals,
.payment-methods {
    width: 100%;
    margin-top: 10px;
}

.item {
    display: flex;
    justify-content: space-between;
    width: 100%;
    padding: 2px 0;
}

.totals .item strong {
    font-weight: bold;
}

.footer {
    font-size: 10px;
    font-weight: bold;
    margin-top: 20px;
}

.modal {
    display: none;
    /* Escondido por padrão */
    position: fixed;
    /* Fixa na tela */
    z-index: 1000;
    /* Coloca acima de outros elementos */
    left: 0;
    top: 0;
    width: 100%;
    /* Largura total */
    height: 100%;
    /* Altura total */
    overflow: auto;
    /* Habilita rolagem se necessário */
    background-color: rgba(0, 0, 0, 0.5);
    /* Fundo semitransparente */
}

.modal-content {
    background-color: #fefefe;
    /* Cor de fundo do modal */
    margin: 15% auto;
    /* Espaçamento em relação ao topo e centralização */
    padding: 20px;
    border: 1px solid #888;
    /* Borda do modal */
    width: 80%;
    /* Largura do modal */
}

.close {
    color: #aaa;
    /* Cor do botão de fechar */
    float: right;
    /* Alinha à direita */
    font-size: 28px;
    /* Tamanho da fonte */
    font-weight: bold;
    /* Negrito */
}

.close:hover,
.close:focus {
    color: black;
    /* Cor ao passar o mouse */
    text-decoration: none;
    /* Remove sublinhado */
    cursor: pointer;
    /* Muda o cursor */
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

@media print {
    .no-print {
        display: none;
    }
}
</style>
</head>

<body>
    <div class="receipt-container">
        <div class="header">
            <img src="../public/assets/images/Logo.png" alt="Lu & Yosh Catering Logo">
            <h2>Lu & Yosh Catering</h2>
            <p>Av. Eduardo Mondlane, 1234<br>Quelimane, Moçambique<br>Tel: +258 21 123 456<br>NUIT: 123456789</p>
            <h3>Recibo da Venda #<?php echo $sale_id; ?></h3>
            <p>Data: <?php echo date('d/m/Y H:i', strtotime($sale['sale_date'])); ?></p>
            <div class="divider"></div>
        </div>

        <div class="items">
            <?php foreach ($sale_items as $item): ?>
            <div class="item">
                <span><?php echo htmlspecialchars(get_product_name($item['product_id'])); ?>
                    x<?php echo $item['quantity']; ?></span>
                <span><?php echo number_format($item['quantity'] * $item['unit_price'], 2); ?> MT</span>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="divider"></div>

        <div class="totals">
            <div class="item">
                <strong>Total:</strong>
                <span><?php echo number_format($sale['total_amount'], 2); ?> MT</span>
            </div>
        </div>
        <div class="divider"></div>
        <div class="footer">
            <p>Obrigado pela sua preferência!</p>
            <p>Este documento não serve como fatura</p>
            <p>Impresso em: <?php echo date('d/m/Y H:i:s'); ?></p>
        </div>

        <div class="no-print button-container">
            <button class="print-button" onclick="openModal()">Imprimir</button>
            <button class="close-button" onclick="closeModal()">Fechar</button>
        </div>
    </div>

    <!-- Modal -->
    <div id="printModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <div class="receipt-container">
                <div class="header">
                    <img src="../public/assets/images/Logo.png" alt="Lu & Yosh Catering Logo">
                    <h2>Lu & Yosh Catering</h2>
                    <h3>Recibo da Venda #<?php echo $sale_id; ?></h3>
                    <p>Data: <?php echo date('d/m/Y H:i', strtotime($sale['sale_date'])); ?></p>
                </div>
                <div class="divider"></div>
                <div class="items">
                    <?php foreach ($sale_items as $item): ?>
                    <div class="item">
                        <span><?php echo htmlspecialchars(get_product_name($item['product_id'])); ?>
                            x<?php echo $item['quantity']; ?></span>
                        <span><?php echo number_format($item['quantity'] * $item['unit_price'], 2); ?> MT</span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="divider"></div>
                <div class="footer">
                    <p>Obrigado pela sua preferência!</p>
                    <p>Este documento não serve como fatura</p>
                    <p>Impresso em: <?php echo date('d/m/Y H:i:s'); ?></p>
                </div>
                <button class="print-button" onclick="printReceipt()">Imprimir</button>
            </div>
        </div>
    </div>

    <script>
    function openModal() {
        document.getElementById("printModal").style.display = "block"; // Exibe o modal
    }

    function closeModal() {
        document.getElementById("printModal").style.display = "none"; // Esconde o modal
    }

    function printReceipt() {
        window.print(); // Imprime o conteúdo
        closeModal(); // Fecha o modal após impressão
    }

    window.onclick = function(event) {
        const modal = document.getElementById("printModal");
        if (event.target === modal) {
            closeModal(); // Fecha o modal se clicar fora
        }
    }
    </script>
</body>

</html>