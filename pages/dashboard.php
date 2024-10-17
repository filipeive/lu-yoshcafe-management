<?php
require_once '../config/config.php';
require_login(); // Verifica se o usuário está logado

// Aqui começa o conteúdo do dashboard, como obter informações
$total_sales_today = get_total_sales_today();
$open_orders = order_get_open_count();
$low_stock_products = get_low_stock_products();
$tables = get_all_tables();
$products = get_all_products();
$weekly_sales = get_weekly_sales(); // Obter dados de vendas semanais
include '../includes/header.php';
?>

<!-- partial -->
<div class="row">
    <div class="card">
        <div class="card-body">
            <div class="tab-content tab-content-basic">
                <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview">
                    <!-- Estatísticas Rápidas - Cards maiores e coloridos -->
                    <div class="row">
                        <div class="col-12 grid-margin stretch-card">
                            <div class="statistics-details d-flex align-items-center justify-content-between">

                                <?php if ($_SESSION['role'] == 'admin'): ?>
                                <div class="card-stat bg-primary text-white rounded p-4 m-2 shadow">
                                    <i class="mdi mdi-cash-multiple display-4"></i>
                                    <p class="statistics-title">Vendas Hoje</p>
                                    <h3 class="rate-percentage">MZN
                                        <?php echo number_format($total_sales_today, 2, ',', '.'); ?></h3>
                                </div>
                                <?php endif;?>
                                <div class="card-stat bg-success text-white rounded p-4 m-2 shadow">
                                    <i class="mdi mdi-cart-plus display-4"></i>
                                    <p class="statistics-title">Pedidos Abertos</p>
                                    <h3 class="rate-percentage"><?php echo $open_orders; ?></h3>
                                </div>
                                <div class="card-stat bg-danger text-white rounded p-4 m-2 shadow">
                                    <i class="mdi mdi-alert-circle display-4"></i>
                                    <p class="statistics-title">Produtos com Estoque Baixo</p>
                                    <h3 class="rate-percentage"><?php echo $low_stock_products; ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ações Rápidas - Botões maiores e coloridos -->
                    <div class="row col-sm-12">
                        <div class="col-lg-8 d-flex flex-column">
                            <div class="row flex-grow">
                                <div class="col-12 grid-margin stretch-card">
                                    <div class="card card-rounded shadow">
                                        <div class="card-body">
                                            <h4 class="card-title card-title-dash">Ações Rápidas</h4>
                                            <div class="row mt-3">
                                                <div class="col-md-4 mb-3">
                                                    <a href="tables.php"
                                                        class="btn btn-lg btn-block btn-outline-primary d-flex align-items-center justify-content-center shadow-sm">
                                                        <i class="mdi mdi-table-large menu-icon"></i> Mesas
                                                    </a>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <a href="orders.php"
                                                        class="btn btn-lg btn-block btn-outline-success d-flex align-items-center justify-content-center shadow-sm">
                                                        <i class="mdi mdi-cart menu-icon"></i> Pedidos
                                                    </a>
                                                </div>
                                                <?php if ($_SESSION['role'] == 'admin'): ?>
                                                <div class="col-md-4 mb-3">
                                                    <a href="products.php"
                                                        class="btn btn-lg btn-block btn-outline-info d-flex align-items-center justify-content-center shadow-sm">
                                                        <i class="mdi mdi-food menu-icon"></i> Produtos
                                                    </a>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <a href="reports.php"
                                                        class="btn btn-lg btn-block btn-outline-warning d-flex align-items-center justify-content-center shadow-sm">
                                                        <i class="mdi mdi-chart-bar menu-icon"></i> Relatórios
                                                    </a>
                                                </div>
                                                <?php endif; ?>
                                                <div class="col-md-4 mb-3">
                                                    <a href="#"
                                                        class="btn btn-lg btn-block btn-outline-danger d-flex align-items-center justify-content-center shadow-sm"
                                                        data-bs-toggle="modal" data-bs-target="#newSaleModal">
                                                        <i class="mdi mdi-cash-register menu-icon"></i> Venda Rápida
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Vendas Semanais - Gráfico -->
                            <div class="col-12 grid-margin stretch-card">
                                <div class="card card-rounded shadow">
                                    <div class="card-body">
                                        <h4 class="card-title">Vendas Semanais</h4>
                                        <canvas id="salesChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Controle de Mesas -->
                        <div class="col-lg-4 d-flex flex-column">
                            <div class="col-12 grid-margin stretch-card">
                                <div class="card card-rounded shadow">
                                    <div class="card-body">
                                        <h4 class="card-title card-title-dash">Controle de Mesas</h4>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Mesa</th>
                                                        <th>Status</th>
                                                        <th>Ação</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($tables as $table): ?>
                                                    <tr>
                                                        <td><?php echo $table['number']; ?></td>
                                                        <td>
                                                            <?php
                                                                    // Mapeamento dos status
                                                                    $status_translation = [
                                                                        'occupied' => 'ocupada',
                                                                        'free' => 'livre'
                                                                    ];
                                                                    $status = $status_translation[$table['status']] ?? $table['status']; // Usa o status original se não houver tradução
                                                                ?>
                                                            <span
                                                                class="badge <?php echo ($table['status'] === 'occupied') ? 'btn-danger' : 'btn-success'; ?>">
                                                                <?php echo $status; ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <a href="tables.php?id=<?php echo $table['id']; ?>"
                                                                class="btn btn-sm btn-primary">Gerenciar</a>
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
        </div>
        <!-- Modal para Nova Venda -->
        <div class="modal fade" id="newSaleModal" tabindex="-1" aria-labelledby="newSaleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="newSaleModalLabel">Nova Venda</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="color:#000">
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
                                <option value="mpesa">E-Mola</option>
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
            <!-- Modal de Recibo -->
            <div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="receiptModalLabel">Recibo da Venda</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" id="receiptContent">
                            <!-- O conteúdo do recibo será inserido aqui -->
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                            <button type="button" class="btn btn-primary" id="printReceiptButton">Imprimir</button>
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
                window.open('print_receipt.php?id=' + saleId, /*'_blank'*/ );
            }
            // Gráfico de Vendas Semanais
            // Obter os dados de vendas semanais do PHP
            const weeklySalesData = <?php echo json_encode($weekly_sales); ?>;

            // Preparar os dados para o gráfico
            const labels = Object.keys(weeklySalesData);
            const data = Object.values(weeklySalesData);

            // Criar o gráfico
            const ctx = document.getElementById('salesChart').getContext('2d');
            const salesChart = new Chart(ctx, {
                type: 'line', // Tipo de gráfico: linha
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Vendas (MZN)',
                        data: data,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderWidth: 1,
                        fill: true,
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Valor (MZN)'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Data'
                            }
                        }
                    }
                }
            });
            </script><br>
            <?php include '../includes/footer.php'; ?>

        </div>
    </div>
</div>