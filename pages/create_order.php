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
    <style>
    .category-pills {
        display: flex;
        overflow-x: auto;
        padding: 1rem 0;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
    }

    .category-pill {
        padding: 0.5rem 1.5rem;
        background-color: #f8f9fa;
        border-radius: 50px;
        cursor: pointer;
        white-space: nowrap;
        transition: all 0.3s ease;
    }

    .category-pill.active {
        background-color: #4B49AC;
        color: white;
    }

    .product-card {
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .product-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .quantity-control {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .quantity-input {
        width: 60px;
        text-align: center;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .order-summary {
        position: sticky;
        top: 20px;
        border: none;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .order-item {
        padding: 0.75rem;
        border-bottom: 1px solid #eee;
    }

    .search-field {
        position: relative;
        margin-bottom: 1.5rem;
    }

    .search-field .input-group {
        background-color: #f8f9fa;
        border-radius: 50px;
        overflow: hidden;
    }

    .search-field input {
        border: none;
        padding: 1rem;
        background-color: transparent;
    }

    .badge-table {
        font-size: 1rem;
        padding: 0.5rem 1rem;
        border-radius: 50px;
    }
    </style>
</head>

<div class="content-wrapper">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h3 class="font-weight-bold mb-0">
                                <i class="mdi mdi-cart-plus text-primary"></i> Novo Pedido
                            </h3>
                            <p class="text-muted">Adicione produtos ao pedido</p>
                        </div>
                        <div class="badge badge-primary badge-table">
                            <i class="mdi mdi-table-furniture"></i> Mesa: <?php echo implode(', ', $table_ids); ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-8">
                            <!-- Barra de busca -->
                            <div class="search-field">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-transparent border-0">
                                            <i class="mdi mdi-magnify text-primary"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control" id="searchProducts"
                                        placeholder="Buscar produtos pelo nome...">
                                </div>
                            </div>

                            <!-- Categorias -->
                            <div class="category-pills">
                                <div class="category-pill active">
                                    <i class="mdi mdi-view-grid"></i> Todos
                                </div>
                                <?php foreach ($categories as $category): ?>
                                <div class="category-pill">
                                    <i class="mdi mdi-tag"></i> <?php echo $category['name']; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Lista de Produtos -->
                            <form method="post" id="orderForm">
                                <input type="hidden" name="table_id" value="<?php echo $table_id; ?>">

                                <div class="row">
                                    <?php foreach ($products as $product): ?>
                                    <div class="col-md-6 col-xl-4 mb-3"
                                        data-category="<?php echo $product['category_id']; ?>">
                                        <div class="card product-card h-100">
                                            <div class="card-body">
                                                <div class="d-flex flex-column h-100">
                                                    <div class="mb-3">
                                                        <h5 class="card-title mb-1">
                                                            <i class="mdi mdi-food"></i> <?php echo $product['name']; ?>
                                                        </h5>
                                                        <h6 class="text-success mb-0">
                                                            <i class="mdi mdi-cash"></i> MZN
                                                            <?php echo number_format($product['price'], 2, ',', '.'); ?>
                                                        </h6>
                                                    </div>
                                                    <div class="quantity-control mt-auto">
                                                        <button type="button"
                                                            class="btn btn-icon btn-outline-primary quantity-btn decrease">
                                                            <i class="mdi mdi-minus"></i>
                                                        </button>
                                                        <input type="number" name="quantities[]"
                                                            class="form-control quantity-input" value="0" min="0">
                                                        <button type="button"
                                                            class="btn btn-icon btn-outline-primary quantity-btn increase">
                                                            <i class="mdi mdi-plus"></i>
                                                        </button>
                                                        <input type="hidden" name="products[]"
                                                            value="<?php echo $product['id']; ?>">
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
                                    <h4 class="card-title">
                                        <i class="mdi mdi-clipboard-text"></i> Resumo do Pedido
                                    </h4>
                                    <div id="orderItems" class="mb-4">
                                        <!-- Items serão adicionados via JavaScript -->
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <h4 class="mb-0">Total:</h4>
                                        <h3 class="text-success mb-0">
                                            <i class="mdi mdi-cash-multiple"></i> MZN <span id="totalPrice">0,00</span>
                                        </h3>
                                    </div>
                                    <button type="submit" form="orderForm"
                                        class="btn btn-primary btn-lg btn-block mb-3">
                                        <i class="mdi mdi-check-circle"></i> Confirmar Pedido
                                    </button>
                                    <a href="tables.php" class="btn btn-outline-danger btn-lg btn-block">
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

<script>document.addEventListener('DOMContentLoaded', function() {
    // Função para atualizar o resumo do pedido
    function updateOrderSummary() {
        const orderItems = document.getElementById('orderItems');
        const totalPriceSpan = document.getElementById('totalPrice');
        let total = 0;
        
        orderItems.innerHTML = '';
        
        document.querySelectorAll('.product-card').forEach(card => {
            const quantity = parseInt(card.querySelector('.quantity-input').value);
            if (quantity > 0) {
                const name = card.querySelector('.card-title').textContent.trim();
                const price = parseFloat(card.querySelector('.text-success').textContent
                    .replace('MZN', '').replace(',', '.').trim());
                const itemTotal = price * quantity;
                total += itemTotal;
                
                orderItems.innerHTML += `
                    <div class="order-item">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="mb-0">${name}</h6>
                                <small class="text-muted">MZN ${price.toFixed(2)} × ${quantity}</small>
                            </div>
                            <h6 class="text-success mb-0">MZN ${itemTotal.toFixed(2)}</h6>
                        </div>
                    </div>
                `;
            }
        });
        
        totalPriceSpan.textContent = total.toFixed(2).replace('.', ',');
    }

    // Eventos para os botões de quantidade
    document.querySelectorAll('.quantity-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.parentElement.querySelector('.quantity-input');
            const currentValue = parseInt(input.value);
            
            if (this.classList.contains('increase')) {
                input.value = currentValue + 1;
            } else if (this.classList.contains('decrease') && currentValue > 0) {
                input.value = currentValue - 1;
            }
            
            updateOrderSummary();
        });
    });

    // Evento para inputs de quantidade
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', updateOrderSummary);
    });

    // Busca de produtos
    const searchInput = document.getElementById('searchProducts');
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        
        document.querySelectorAll('.product-card').forEach(card => {
            const productName = card.querySelector('.card-title').textContent.toLowerCase();
            const productContainer = card.closest('[data-category]');
            
            if (productName.includes(searchTerm)) {
                productContainer.style.display = '';
            } else {
                productContainer.style.display = 'none';
            }
        });
    });

    // Filtro por categoria
    document.querySelectorAll('.category-pill').forEach(pill => {
        pill.addEventListener('click', function() {
            document.querySelectorAll('.category-pill').forEach(p => p.classList.remove('active'));
            this.classList.add('active');
            
            const category = this.getAttribute('data-category');
            
            document.querySelectorAll('[data-category]').forEach(product => {
                if (!category || category === 'all' || product.getAttribute('data-category') === category) {
                    product.style.display = '';
                } else {
                    product.style.display = 'none';
                }
            });
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>