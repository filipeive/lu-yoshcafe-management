<?php
require_once '../config/config.php';
require_login();

$pageTitle = "Vendas";
include '../includes/header.php';

$sales = get_all_sales();
$products = get_all_products();
?>

<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Vendas</h4>
                <p class="mb-md-0">Gerencie suas vendas aqui.</p><br>
                <p class="card-description">
                    <button type="button" class="btn btn-primary mt-2 mt-xl-0" data-bs-toggle="modal"
                        data-bs-target="#newSaleModal">
                        Nova Venda
                    </button>
                </p>
                <div class="row">
                    <div class="col-md-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Histórico de Vendas</h4>
                                <div class="table-responsive">
                                    <table class="table table-hover">
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
                                                <td><?php echo $sale['id']; ?></td>
                                                <td><?php echo $sale['sale_date']; ?></td>
                                                <td>MZN <?php echo number_format($sale['total_amount'], 2); ?></td>
                                                <td><?php echo $sale['payment_method']; ?></td>
                                                <td><?php echo $sale['status']; ?></td>
                                                <td>
                                                    <button class="btn btn-outline-primary btn-sm"
                                                        onclick="viewSaleDetails(<?php echo $sale['id']; ?>)">Ver
                                                        Detalhes</button>
                                                    <button class="btn btn-outline-info btn-sm"
                                                        onclick="printReceipt(<?php echo $sale['id']; ?>)">Imprimir
                                                        Recibo</button>
                                                    <button class="btn btn-outline-danger btn-sm"
                                                        onclick="delete_order(<?php echo $sale['id']; ?>)">Cancelar
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal para Nova Venda -->
    <div class="modal fade" id="newSaleModal" tabindex="-1" aria-labelledby="newSaleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newSaleModalLabel">Nova Venda</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="newSaleForm">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="product" class="form-label">Produto</label>
                                <select class="form-select" id="product" required>
                                    <?php foreach ($products as $product): ?>
                                    <option value="<?php echo $product['id']; ?>"
                                        data-price="<?php echo $product['price']; ?>">
                                        <?php echo $product['name']; ?> - MZN
                                        <?php echo number_format($product['price'], 2); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="quantity" class="form-label">Quantidade</label>
                                <input type="number" class="form-control" id="quantity" min="1" value="1" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <button type="button" class="btn btn-primary form-control"
                                    id="addItemButton">Adicionar</button>
                            </div>
                        </div>
                    </form>
                    <table class="table" id="saleItemsTable">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Quantidade</th>
                                <th>Preço Unitário</th>
                                <th>Total</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3">Total da Venda</th>
                                <th id="saleTotalAmount">MZN 0.00</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                    <div class="mb-3">
                        <label for="paymentMethod" class="form-label">Método de Pagamento</label>
                        <select class="form-select" id="paymentMethod" required>
                            <option value="cash">Dinheiro</option>
                            <option value="card">Cartão</option>
                            <option value="mpesa">M-Pesa</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="finalizeSaleButton">Finalizar
                        Venda</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
let saleItems = [];

document.getElementById('addItemButton').addEventListener('click', function() {
    const productSelect = document.getElementById('product');
    const product = productSelect.options[productSelect.selectedIndex];
    const quantity = document.getElementById('quantity').value;
    const price = parseFloat(product.dataset.price);

    if (quantity <= 0) {
        alert('Por favor, insira uma quantidade válida.');
        return;
    }

    const item = {
        id: product.value,
        name: product.text.split(' - ')[0], // Pega apenas o nome do produto
        quantity: parseInt(quantity),
        price: price,
        total: price * parseInt(quantity)
    };

    saleItems.push(item);
    updateSaleItemsTable();
    
    // Resetar campos
    document.getElementById('quantity').value = 1;
    productSelect.selectedIndex = 0;
});

function updateSaleItemsTable() {
    const tableBody = document.querySelector('#saleItemsTable tbody');
    const totalAmountElement = document.getElementById('saleTotalAmount');
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

    totalAmountElement.textContent = `MZN ${totalAmount.toFixed(2)}`;
}

function removeItem(index) {
    saleItems.splice(index, 1);
    updateSaleItemsTable();
}

document.getElementById('finalizeSaleButton').addEventListener('click', function() {
    if (saleItems.length === 0) {
        alert('Por favor, adicione itens à venda antes de finalizar.');
        return;
    }

    const paymentMethod = document.getElementById('paymentMethod').value;

    fetch('gerir_vendas/process_sale.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            items: saleItems,
            paymentMethod: paymentMethod
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Venda realizada com sucesso!');
            // Abrir o recibo em uma nova janela
            window.open(data.receiptUrl, '_blank');
            // Fechar o modal e limpar os itens
            $('#newSaleModal').modal('hide');
            saleItems = [];
            updateSaleItemsTable();
            // Recarregar a página para atualizar o histórico de vendas
            location.reload();
        } else {
            alert('Erro ao realizar a venda: ' + data.message);
        }
    })
    .catch((error) => {
        console.error('Error:', error);
        alert('Erro ao processar a venda.');
    });
});

function viewSaleDetails(saleId) {
    window.location.href = 'sale_details.php?id=' + saleId;
}

function printReceipt(saleId) {
    window.open('print_receipt.php?id=' + saleId, /*'_blank'*/);
}
</script>

<?php include '../includes/footer.php'; ?>




<?php
require_once '../config/config.php';
require_login();

$pageTitle = "Vendas";
include '../includes/header.php';

// Pagination setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 7;
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
                <p class="mb-md-0">Gerencie suas vendas aqui.</p><br>
                <p class="card-description">
                    <button type="button" class="btn btn-primary mt-2 mt-xl-0" data-bs-toggle="modal"
                        data-bs-target="#newSaleModal">
                        Nova Venda
                    </button>
                </p>
                <div class="row">
                    <div class="col-md-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Histórico de Vendas</h4>
                                <div class="table-responsive">
                                    <table class="table table-hover">
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
                                                <td><?php echo $sale['id']; ?></td>
                                                <td><?php echo $sale['sale_date']; ?></td>
                                                <td>MZN <?php echo number_format($sale['total_amount'], 2); ?></td>
                                                <td><?php echo $sale['payment_method']; ?></td>
                                                <td><?php echo $sale['status']; ?></td>
                                                <td>
                                                    <button class="btn btn-outline-primary btn-sm"
                                                        onclick="viewSaleDetails(<?php echo $sale['id']; ?>)">Ver
                                                        Detalhes</button>
                                                    <button class="btn btn-outline-info btn-sm"
                                                        onclick="printReceipt(<?php echo $sale['id']; ?>)">Imprimir
                                                        Recibo</button>
                                                    <button class="btn btn-outline-danger btn-sm"
                                                        onclick="delete_order(<?php echo $sale['id']; ?>)">Cancelar
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
    <!-- Modal para Nova Venda -->
    <!-- ... (rest of the modal code remains unchanged) ... -->
</div>

<script>
// ... (rest of the JavaScript code remains unchanged) ...
</script>

<?php include '../includes/footer.php'; ?>