<?php
require_once '../config/config.php';
require_login();

$pageTitle = "Vendas";
include '../includes/header.php';

// Helper functions for StarAdmin2 specific icons and classes
function get_payment_icon_mdi($method) {
    $icons = [
        'cash' => 'mdi-cash',
        'credit' => 'mdi-credit-card',
        'debit' => 'mdi-credit-card-outline',
        'transfer' => 'mdi-bank-transfer',
        'pix' => 'mdi-qrcode'
    ];
    return $icons[$method] ?? 'mdi-help-circle';
}

function get_status_class_staradmin($status) {
    $classes = [
        'completed' => 'badge-success',
        'pending' => 'badge-warning',
        'cancelled' => 'badge-danger',
        'processing' => 'badge-info'
    ];
    return $classes[$status] ?? 'badge-secondary';
}
// Pagination setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 6;
$offset = ($page - 1) * $per_page;

// Get total number of sales and paginated sales
$total_sales = count_all_sales();
$total_pages = ceil($total_sales / $per_page);
$sales = get_paginated_sales($offset, $per_page);

$products = get_all_products();
?>
<!-- Add this to your CSS file -->
<style>
.border-left-primary {
    border-left: 4px solid #4e73df !important;
}

.border-left-success {
    border-left: 4px solid #1cc88a !important;
}

.border-left-info {
    border-left: 4px solid #36b9cc !important;
}

.border-left-warning {
    border-left: 4px solid #f6c23e !important;
}

.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-5px);
}

.table th {
    background-color: #f8f9fc;
    border-top: none;
}

.badge {
    padding: 0.5em 1em;
}

.btn-group .btn {
    padding: 0.375rem 0.75rem;
}

.dropdown-item i {
    width: 1rem;
    margin-right: 0.5rem;
}

.pagination {
    margin-bottom: 0;
}

.card-rounded {
    border-radius: 12px;
}

.icon-md {
    font-size: 24px;
}

.badge {
    padding: 5px 12px;
    font-weight: 500;
    border-radius: 4px;
}

.badge-success {
    background-color: rgba(57, 198, 138, 0.15);
    color: #39c68a;
}

.badge-warning {
    background-color: rgba(255, 193, 7, 0.15);
    color: #ffc107;
}

.badge-danger {
    background-color: rgba(242, 78, 78, 0.15);
    color: #f24e4e;
}

.badge-info {
    background-color: rgba(0, 188, 212, 0.15);
    color: #00bcd4;
}

.btn-icon {
    width: 32px;
    height: 32px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.table> :not(caption)>*>* {
    padding: 0.75rem 1.25rem;
}

.gap-2 {
    gap: 0.5rem;
}

.pagination-rounded .page-link {
    border-radius: 50%;
    margin: 0 3px;
    width: 32px;
    height: 32px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
<div class="content-wrapper">
    <div class="row">
        <!-- Stats Cards Row -->
        <div class="col-sm-12 col-lg-3 grid-margin stretch-card">
            <div class="card card-rounded">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h4 class="card-title card-title-dash">Vendas Totais</h4>
                        <i class="mdi mdi-cash-multiple text-primary icon-md"></i>
                    </div>
                    <div class="mt-3">
                        <h2 class="rate-percentage">MZN <?php echo number_format(get_total_sales_amount(), 2); ?></h2>
                        <p class="text-success mb-0">
                            <i class="mdi mdi-trending-up"></i> Total acumulado
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-12 col-lg-3 grid-margin stretch-card">
            <div class="card card-rounded">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h4 class="card-title card-title-dash">Vendas Hoje</h4>
                        <i class="mdi mdi-calendar-today text-success icon-md"></i>
                    </div>
                    <div class="mt-3">
                        <h2 class="rate-percentage">MZN <?php echo number_format(get_today_sales_amount(), 2); ?></h2>
                        <p class="text-success mb-0">
                            <i class="mdi mdi-clock"></i> Hoje
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-12 col-lg-3 grid-margin stretch-card">
            <div class="card card-rounded">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h4 class="card-title card-title-dash">Total Transações</h4>
                        <i class="mdi mdi-receipt text-info icon-md"></i>
                    </div>
                    <div class="mt-3">
                        <h2 class="rate-percentage"><?php echo $total_sales; ?></h2>
                        <p class="text-info mb-0">
                            <i class="mdi mdi-chart-line"></i> Transações realizadas
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-12 col-lg-3 grid-margin stretch-card">
            <div class="card card-rounded">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h4 class="card-title card-title-dash">Pendentes</h4>
                        <i class="mdi mdi-clock-alert text-warning icon-md"></i>
                    </div>
                    <div class="mt-3">
                        <h2 class="rate-percentage"><?php echo get_pending_sales_count(); ?></h2>
                        <p class="text-warning mb-0">
                            <i class="mdi mdi-alert-circle"></i> Aguardando processamento
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales Table -->
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card card-rounded">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title">Histórico de Vendas</h4>
                        <div class="d-flex align-items-center">
                            <div class="input-group" style="width: 250px;">
                                <input type="text" class="form-control" placeholder="Pesquisar vendas..."
                                    id="salesSearch">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="button">
                                        <i class="mdi mdi-magnify"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-primary ms-3" data-bs-toggle="modal"
                                data-bs-target="#newSaleModal">
                                <i class="mdi mdi-plus"></i> Nova Venda
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
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
                                    <td class="text-muted">#<?php echo str_pad($sale['id'], 5, '0', STR_PAD_LEFT); ?>
                                    </td>
                                    <td>
                                        <div class="d-flex">
                                            <i class="mdi mdi-calendar me-2 text-primary"></i>
                                            <?php echo date('d/m/Y H:i', strtotime($sale['sale_date'])); ?>
                                        </div>
                                    </td>
                                    <td class="font-weight-bold">MZN
                                        <?php echo number_format($sale['total_amount'], 2); ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i
                                                class="mdi <?php echo get_payment_icon_mdi($sale['payment_method']); ?> me-2"></i>
                                            <?php echo ucfirst($sale['payment_method']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="badge <?php echo get_status_class_staradmin($sale['status']); ?>">
                                            <?php echo ucfirst($sale['status']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-outline-primary btn-icon btn-rounded"
                                                onclick="viewSaleDetails(<?php echo $sale['id']; ?>)">
                                                <i class="mdi mdi-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-info btn-icon btn-rounded"
                                                onclick="printReceipt(<?php echo $sale['id']; ?>)">
                                                <i class="mdi mdi-printer"></i>
                                            </button>
                                            <div class="dropdown">
                                                <button class="btn btn-outline-secondary btn-icon btn-rounded"
                                                    type="button" data-bs-toggle="dropdown">
                                                    <i class="mdi mdi-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="#">
                                                            <i class="mdi mdi-pencil me-2"></i> Editar
                                                        </a></li>
                                                    <li><a class="dropdown-item" href="#">
                                                            <i class="mdi mdi-file-pdf me-2"></i> Exportar PDF
                                                        </a></li>
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <li><a class="dropdown-item text-danger" href="#">
                                                            <i class="mdi mdi-delete me-2"></i> Excluir
                                                        </a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <p class="text-muted mb-0">
                            Mostrando <?php echo ($offset + 1); ?> até
                            <?php echo min($offset + $per_page, $total_sales); ?> de <?php echo $total_sales; ?>
                            registros
                        </p>
                        <nav>
                            <ul class="pagination pagination-rounded">
                                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>">
                                        <i class="mdi mdi-chevron-left"></i>
                                    </a>
                                </li>
                                <?php for($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>">
                                        <i class="mdi mdi-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
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
    <?php include '../includes/footer.php'; ?>
</div>