<?php
require_once '../config/config.php';
require_login();

$pageTitle = "Vendas";
include '../includes/header.php';

// Pagination setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 5;
$offset = ($page - 1) * $per_page;

// Get total number of sales and paginated sales
$total_sales = count_all_sales();
$total_pages = ceil($total_sales / $per_page);
$sales = get_paginated_sales($offset, $per_page);

$products = get_all_products();
?>

<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Vendas</h4>
                <p class="card-description">
                    <button type="button" class="btn btn-success mt-2 mt-xl-0" data-bs-toggle="modal"
                        data-bs-target="#newSaleModal" style="padding:10px 20px;">
                        Nova Venda
                    </button>
                </p>
                <div class="row">
                    <div class="col-md-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Histórico de Vendas</h4>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>ID</th>
                                                <th>Data</th>
                                                <th>Total</th>
                                                <th>Método de Pagamento</th>
                                                <th>Status</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($sales as $sale): ?>
                                            <tr>
                                                <td><?php echo $sale['id']; ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($sale['sale_date'])); ?></td>
                                                <td>MZN <?php echo number_format($sale['total_amount'], 2); ?></td>
                                                <td><?php echo ucfirst($sale['payment_method']); ?></td>
                                                <td>
                                                    <span
                                                        class="badge <?php echo $sale['status'] === 'completed' ? 'bg-success' : 'bg-warning'; ?>">
                                                        <?php echo ucfirst($sale['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-outline-primary btn-sm"
                                                        onclick="viewSaleDetails(<?php echo $sale['id']; ?>)">
                                                        Ver Detalhes
                                                    </button>
                                                    <button class="btn btn-outline-info btn-sm"
                                                        onclick="printReceipt(<?php echo $sale['id']; ?>)">
                                                        Imprimir Recibo
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <!-- Pagination controls -->
                                <div class="d-flex justify-content-between mt-4">
                                    <?php if ($page > 1): ?>
                                    <a href="?page=<?php echo $page - 1; ?>" class="btn btn-primary">Anterior</a>
                                    <?php else: ?>
                                    <button class="btn btn-primary" disabled>Anterior</button>
                                    <?php endif; ?>

                                    <span>Página <?php echo $page; ?> de <?php echo $total_pages; ?></span>

                                    <?php if ($page < $total_pages): ?>
                                    <a href="?page=<?php echo $page + 1; ?>" class="btn btn-primary">Próximo</a>
                                    <?php else: ?>
                                    <button class="btn btn-primary" disabled>Próximo</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
</div>
<?php include 'modais/modais_venda.php'; ?>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
// Variáveis globais
let saleItems = [];

// Função para adicionar item à venda
document.getElementById('addItemButton').addEventListener('click', function() {
    const productSelect = document.getElementById('product');
    const product = productSelect.options[productSelect.selectedIndex];
    const quantity = parseInt(document.getElementById('quantity').value);
    const price = parseFloat(product.dataset.price);

    if (isNaN(quantity) || quantity <= 0) {
        showAlert('Erro', 'Por favor, insira uma quantidade válida.', 'error');
        return;
    }

    const item = {
        id: product.value,
        name: product.text.split(' - ')[0],
        quantity: quantity,
        price: price,
        total: price * quantity
    };

    saleItems.push(item);
    updateSaleItemsTable();
    resetInputFields();
});

// Função para atualizar a tabela de itens da venda
function updateSaleItemsTable() {
    const tableBody = document.querySelector('#saleItemsTable tbody');
    const saleTotalAmount = document.getElementById('saleTotalAmount');
    let totalAmount = 0;

    tableBody.innerHTML = '';
    saleItems.forEach((item, index) => {
        const row = tableBody.insertRow();
        row.innerHTML = `
    <td>${item.name}</td>
    <td>${item.quantity}</td>
    <td>MZN ${item.price.toFixed(2)}</td>
    <td>MZN ${item.total.toFixed(2)}</td>
    <td><button class="btn btn-danger btn-sm" onclick="removeItem(${index})">Remover</button></td>
`;
        totalAmount += item.total;
    });

    saleTotalAmount.textContent = `MZN ${totalAmount.toFixed(2)}`;
    calculateChange();
}

// Função para remover item da venda
function removeItem(index) {
    saleItems.splice(index, 1);
    updateSaleItemsTable();
}

// Evento para finalizar a venda
document.getElementById('finalizeSaleButton').addEventListener('click', function() {
    if (saleItems.length === 0) {
        showAlert('Erro', 'Por favor, adicione itens à venda antes de finalizar.', 'error');
        return;
    }

    const totalAmount = saleItems.reduce((total, item) => total + item.total, 0);
    const totalPaid = calculateTotalPaid();

    if (totalPaid < totalAmount) {
        showAlert('Erro', 'O valor pago é menor que o total da venda.', 'error');
        return;
    }

    showConfirmDialog('Finalizar Venda', 'Deseja imprimir a conta antes de finalizar?',
        function() {
            printReceipt();
        },
        function() {
            processSale();
        }
    );
});

// Função para imprimir o recibo
function printReceipt() {
    let receiptContent = generateReceiptContent();
    const printWindow = window.open('', '_blank');
    printWindow.document.write(receiptContent);
    printWindow.document.close();
    printWindow.print();
}

// Função para processar a venda
function processSale() {
    const saleData = {
        items: saleItems,
        cashPayment: parseFloat(document.getElementById('cashPayment').value) || 0,
        cardPayment: parseFloat(document.getElementById('cardPayment').value) || 0,
        mpesaPayment: parseFloat(document.getElementById('mpesaPayment').value) || 0,
        emolaPayment: parseFloat(document.getElementById('emolaPayment').value) || 0
    };

    fetch('gerir_vendas/process_sale.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(saleData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Abrir o recibo em uma nova janela
                window.open(data.receiptUrl, '_blank');

                showAlert('Sucesso', 'Venda realizada com sucesso!', 'success')
                    .then(() => {
                        resetSale();
                        location.reload();
                    });
            } else {
                showAlert('Erro', 'Erro ao realizar a venda: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Erro', 'Erro ao processar a venda.', 'error');
        });
}

// Funções auxiliares
function calculateTotalPaid() {
    return ['cashPayment', 'cardPayment', 'mpesaPayment', 'emolaPayment']
        .reduce((total, id) => total + (parseFloat(document.getElementById(id).value) || 0), 0);
}

function calculateChange() {
    const totalAmount = saleItems.reduce((total, item) => total + item.total, 0);
    const totalPaid = calculateTotalPaid();
    const change = totalPaid - totalAmount;
    document.getElementById('change').value = `MZN ${Math.max(change, 0).toFixed(2)}`;
}

function resetInputFields() {
    document.getElementById('quantity').value = 1;
    document.getElementById('product').selectedIndex = 0;
}

function resetSale() {
    saleItems = [];
    updateSaleItemsTable();
    document.querySelectorAll('#cashPayment, #cardPayment, #mpesaPayment, #emolaPayment').forEach(input => {
        input.value = '0';
    });
    calculateChange();
}

function showAlert(title, text, icon) {
    return Swal.fire({
        title,
        text,
        icon
    });
}

function showConfirmDialog(title, text, confirmCallback, cancelCallback) {
    Swal.fire({
        title,
        text,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sim, imprimir',
        cancelButtonText: 'Não, finalizar'
    }).then((result) => {
        if (result.isConfirmed) {
            confirmCallback();
        } else {
            cancelCallback();
        }
    });
}

function generateReceiptContent(isPreview = false) {
    let content = `
<html>
<head>
    <title>${isPreview ? 'Pré-visualização da Conta' : 'Recibo da Venda'}</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid black; padding: 5px; text-align: left; }
        .header { text-align: center; margin-bottom: 20px; }
        .footer { margin-top: 20px; text-align: center; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Farmácia Lu Yosh</h2>
        <p>Av. Eduardo Mondlane, 1234</p>
        <p>Maputo, Moçambique</p>
        <p>Tel: +258 21 123 456</p>
        <p>NUIT: 123456789</p>
        <h3>${isPreview ? 'Pré-visualização da Conta' : 'Recibo da Venda'}</h3>
        <p>Data: ${new Date().toLocaleString()}</p>
    </div>
    <table>
        <thead>
            <tr>
                <th>Produto</th>
                <th>Quantidade</th>
                <th>Preço Unitário</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
`;

    saleItems.forEach(item => {
        content += `
    <tr>
        <td>${item.name}</td>
        <td>${item.quantity}</td>
        <td>MZN ${item.price.toFixed(2)}</td>
        <td>MZN ${item.total.toFixed(2)}</td>
    </tr>
`;
    });

    const totalAmount = saleItems.reduce((total, item) => total + item.total, 0);
    content += `
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3">Total</th>
                <th>MZN ${totalAmount.toFixed(2)}</th>
            </tr>
        </tfoot>
    </table>
`;

    if (!isPreview) {
        const cashPayment = parseFloat(document.getElementById('cashPayment').value) || 0;
        const cardPayment = parseFloat(document.getElementById('cardPayment').value) || 0;
        const mpesaPayment = parseFloat(document.getElementById('mpesaPayment').value) || 0;
        const emolaPayment = parseFloat(document.getElementById('emolaPayment').value) || 0;

        content += `
    <h4>Métodos de Pagamento:</h4>
    <ul>
        ${cashPayment > 0 ? `<li>Dinheiro: MZN ${cashPayment.toFixed(2)}</li>` : ''}
        ${cardPayment > 0 ? `<li>Cartão: MZN ${cardPayment.toFixed(2)}</li>` : ''}
        ${mpesaPayment > 0 ? `<li>M-Pesa: MZN ${mpesaPayment.toFixed(2)}</li>` : ''}
        ${emolaPayment > 0 ? `<li>Emola: MZN ${emolaPayment.toFixed(2)}</li>` : ''}
    </ul>
`;

        const totalPaid = cashPayment + cardPayment + mpesaPayment + emolaPayment;
        const change = totalPaid - totalAmount;

        if (change > 0) {
            content += `<p><strong>Troco: MZN ${change.toFixed(2)}</strong></p>`;
        }
    }

    content += `
    <div class="footer">
        <p>Obrigado pela sua preferência!</p>
        <p>Para mais informações, visite www.farmacialuyosh.co.mz</p>
        <p>${isPreview ? 'Esta é uma pré-visualização e não um recibo oficial' : 'Este documento não serve como fatura'}</p>
    </div>
</body>
</html>
`;

    return content;
}

// Função para imprimir o recibo
function printReceipt(isPreview = false) {
    let receiptContent = generateReceiptContent(isPreview);
    const printWindow = window.open('', '_blank');
    printWindow.document.write(receiptContent);
    printWindow.document.close();
    printWindow.print();
    printWindow.close();
}

// Evento para finalizar a venda
document.getElementById('finalizeSaleButton').addEventListener('click', function() {
    if (saleItems.length === 0) {
        showAlert('Erro', 'Por favor, adicione itens à venda antes de finalizar.', 'error');
        return;
    }

    const totalAmount = saleItems.reduce((total, item) => total + item.total, 0);
    const totalPaid = calculateTotalPaid();

    if (totalPaid < totalAmount) {
        showAlert('Erro', 'O valor pago é menor que o total da venda.', 'error');
        return;
    }

    showConfirmDialog('Finalizar Venda', 'Deseja imprimir a conta antes de finalizar?',
        function() {
            printReceipt(true); // Imprimir pré-visualização
        },
        function() {
            processSale();
        }
    );
});

// Event listeners para cálculo de troco
document.querySelectorAll('#cashPayment, #cardPayment, #mpesaPayment, #emolaPayment').forEach(input => {
    input.addEventListener('input', calculateChange);
});

// Função para visualizar detalhes da venda
function viewSaleDetails(saleId) {
    window.location.href = 'sale_details.php?id=' + saleId;
}


function printReceipt(saleId) {
    window.open('print_receipt.php?id=' + saleId, /*'_blank'*/ );
}
</script>