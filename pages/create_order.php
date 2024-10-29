<?php
require_once '../config/config.php';
require_login();
$table_id = isset($_GET['table_id']) ? intval($_GET['table_id']) : 0;
$table_ids = [];

if ($table_id > 0) {
    $stmt = $pdo->prepare("SELECT group_id FROM tables WHERE id = ?");
    $stmt->execute([$table_id]);
    $group = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($group && $group['group_id']) {
        $stmt = $pdo->prepare("SELECT id FROM tables WHERE group_id = ?");
        $stmt->execute([$group['group_id']]);
        $table_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } else {
        $table_ids = [$table_id];
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $products = $_POST['products'];
    $quantities = $_POST['quantities'];
    $order_id = order_create($table_ids);
    
    foreach ($table_ids as $table_id) {
        for ($i = 0; $i < count($products); $i++) {
            if ($quantities[$i] > 0) {
                order_add_item($order_id, $products[$i], $quantities[$i]);
            }
        }
    }
    
    foreach ($table_ids as $table_id) {
        update_table_status($table_id, 'occupied');
    }
    
    header("Location: view_order.php?id=$order_id");
    exit;
}

$products = product_get_available();
$categories = []; // Adicione aqui a lógica para buscar categorias
include '../includes/header.php';
?>
<head>
    <link rel="stylesheet" href="assets/order.css">
</head>
<div class="content-wrapper">
    <div class="row">
        <div class="col-12 grid-margin">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title mb-0">Novo Pedido</h4>
                        <div class="badge badge-primary">
                            Mesa: <?php echo implode(', ', $table_ids); ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-8">
                            <!-- Barra de busca -->
                            <div class="form-group search-field">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-transparent">
                                            <i class="mdi mdi-magnify"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control" id="searchProducts" placeholder="Buscar produtos...">
                                </div>
                            </div>

                            <!-- Categorias -->
                            <div class="category-pills">
                                <div class="category-pill active">Todos</div>
                                <?php foreach ($categories as $category): ?>
                                    <div class="category-pill"><?php echo $category['name']; ?></div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Lista de Produtos -->
                            <form method="post" id="orderForm">
                                <input type="hidden" name="table_id" value="<?php echo $table_id; ?>">
                                
                                <div class="row">
                                    <?php foreach ($products as $product): ?>
                                        <div class="col-12 grid-margin" data-category="<?php echo $product['category_id']; ?>">
                                            <div class="card product-card">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <h5 class="card-title mb-1"><?php echo $product['name']; ?></h5>
                                                            <p class="text-muted mb-0">MZN <?php echo number_format($product['price'], 2, ',', '.'); ?></p>
                                                        </div>
                                                        <div class="quantity-control">
                                                            <button type="button" class="btn btn-icon btn-outline-secondary quantity-btn decrease">
                                                                <i class="mdi mdi-minus"></i>
                                                            </button>
                                                            <input type="number" name="quantities[]" class="form-control quantity-input" value="0" min="0">
                                                            <button type="button" class="btn btn-icon btn-outline-secondary quantity-btn increase">
                                                                <i class="mdi mdi-plus"></i>
                                                            </button>
                                                            <input type="hidden" name="products[]" value="<?php echo $product['id']; ?>">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </form>
                        </div>

                        <!-- Resumo do Pedido -->
                        <div class="col-lg-4">
                            <div class="card order-summary">
                                <div class="card-body">
                                    <h4 class="card-title">Resumo do Pedido</h4>
                                    <div id="orderItems" class="mb-4">
                                        <!-- Items serão adicionados via JavaScript -->
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <h4 class="mb-0">Total:</h4>
                                        <h3 class="text-success mb-0">MZN <span id="totalPrice">0,00</span></h3>
                                    </div>
                                    <button type="submit" form="orderForm" class="btn btn-primary btn-lg btn-block mb-2">
                                        <i class="mdi mdi-check-circle"></i> Confirmar Pedido
                                    </button>
                                    <a href="tables.php" class="btn btn-outline-secondary btn-lg btn-block">
                                        <i class="mdi mdi-close-circle"></i> Cancelar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Função para atualizar o resumo do pedido
    function updateOrderSummary() {
        const orderItems = document.getElementById('orderItems');
        const totalPriceElement = document.getElementById('totalPrice');
        let total = 0;
        orderItems.innerHTML = '';

        document.querySelectorAll('.product-card').forEach(card => {
            const quantity = parseInt(card.querySelector('.quantity-input').value);
            if (quantity > 0) {
                const name = card.querySelector('.card-title').textContent;
                const price = parseFloat(card.querySelector('.text-muted').textContent
                    .replace('MZN ', '').replace(',', '.'));
                const itemTotal = price * quantity;
                total += itemTotal;

                orderItems.innerHTML += `
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <span class="text-primary">${quantity}x</span> ${name}
                        </div>
                        <div class="text-muted">MZN ${itemTotal.toFixed(2).replace('.', ',')}</div>
                    </div>
                `;
            }
        });

        totalPriceElement.textContent = total.toFixed(2).replace('.', ',');
    }

    // Manipuladores de eventos para os botões de quantidade
    document.querySelectorAll('.quantity-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const input = this.parentNode.querySelector('.quantity-input');
            let value = parseInt(input.value);

            if (this.classList.contains('decrease')) {
                value = Math.max(0, value - 1);
            } else {
                value++;
            }

            input.value = value;
            updateOrderSummary();
            
            const card = this.closest('.product-card');
            card.classList.toggle('selected', value > 0);
        });
    });

    // Busca de produtos
    document.getElementById('searchProducts').addEventListener('input', function(e) {
        const search = e.target.value.toLowerCase();
        document.querySelectorAll('.product-card').forEach(card => {
            const parent = card.closest('.grid-margin');
            const name = card.querySelector('.card-title').textContent.toLowerCase();
            parent.style.display = name.includes(search) ? 'block' : 'none';
        });
    });

    // Filtro por categoria
    document.querySelectorAll('.category-pill').forEach(pill => {
        pill.addEventListener('click', function() {
            document.querySelectorAll('.category-pill').forEach(p => p.classList.remove('active'));
            this.classList.add('active');
            
            const category = this.textContent;
            document.querySelectorAll('[data-category]').forEach(item => {
                if (category === 'Todos' || item.dataset.category === category) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });

    // Atualizar quando quantidade é digitada manualmente
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('input', function() {
            updateOrderSummary();
            this.closest('.product-card').classList.toggle('selected', parseInt(this.value) > 0);
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>