<?php
// Arquivo: create_order.php
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

// Obter produtos disponíveis agrupados por categoria
$stmt = $pdo->query("SELECT p.*, c.name as category_name 
                     FROM products p 
                     LEFT JOIN categories c ON p.category_id = c.id 
                     WHERE p.active = 1 
                     ORDER BY c.name, p.name");
$products_by_category = [];
while ($product = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $category = $product['category_name'] ?? 'Sem Categoria';
    if (!isset($products_by_category[$category])) {
        $products_by_category[$category] = [];
    }
    $products_by_category[$category][] = $product;
}

include '../includes/header.php';
?>

<section class="section">
    <div class="section-header">
        <h1>Criar Novo Pedido</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></div>
            <div class="breadcrumb-item">Criar Pedido</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <!-- Lista de Produtos -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4>Produtos Disponíveis</h4>
                        <div class="card-header-form">
                            <div class="input-group">
                                <input type="text" class="form-control" id="searchProducts" placeholder="Buscar produtos...">
                                <div class="input-group-btn">
                                    <button class="btn btn-primary"><i class="fas fa-search"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="post" id="orderForm">
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        Mesa(s): <?php echo implode(', ', $table_ids); ?>
                                    </div>
                                </div>
                            </div>

                            <?php foreach ($products_by_category as $category => $products): ?>
                            <div class="products-category">
                                <h6 class="bg-light p-2 mb-3 rounded">
                                    <i class="fas fa-tags"></i> <?php echo htmlspecialchars($category); ?>
                                </h6>
                                <div class="row product-list">
                                    <?php foreach ($products as $product): ?>
                                    <div class="col-md-6 col-lg-4 mb-3 product-item">
                                        <div class="card card-primary">
                                            <div class="card-body">
                                                <div class="product-details">
                                                    <h6 class="product-name">
                                                        <?php echo htmlspecialchars($product['name']); ?>
                                                    </h6>
                                                    <p class="text-muted">
                                                        R$ <?php echo number_format($product['price'], 2, ',', '.'); ?>
                                                    </p>
                                                </div>
                                                <div class="quantity-controls">
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <button type="button" class="btn btn-secondary btn-decrease">
                                                                <i class="fas fa-minus"></i>
                                                            </button>
                                                        </div>
                                                        <input type="hidden" name="products[]" value="<?php echo $product['id']; ?>">
                                                        <input type="number" name="quantities[]" 
                                                               class="form-control text-center quantity-input" 
                                                               value="0" min="0" 
                                                               data-price="<?php echo $product['price']; ?>">
                                                        <div class="input-group-append">
                                                            <button type="button" class="btn btn-secondary btn-increase">
                                                                <i class="fas fa-plus"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Resumo do Pedido -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h4>Resumo do Pedido</h4>
                    </div>
                    <div class="card-body">
                        <div id="orderSummary">
                            <div class="alert alert-light">
                                Selecione os produtos para criar o pedido
                            </div>
                        </div>
                        <div class="total-section">
                            <h5 class="text-right">
                                Total: R$ <span id="orderTotal">0,00</span>
                            </h5>
                        </div>
                    </div>
                    <div class="card-footer bg-whitesmoke">
                        <button type="submit" form="orderForm" class="btn btn-primary btn-lg btn-block">
                            <i class="fas fa-check-circle"></i> Criar Pedido
                        </button>
                        <a href="tables.php" class="btn btn-light btn-lg btn-block mt-2">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.product-item .card {
    transition: all 0.3s ease;
    border: 1px solid #e4e6fc;
}

.product-item .card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.quantity-controls .input-group {
    width: 120px;
    margin: 0 auto;
}

.product-name {
    margin-bottom: 5px;
    font-size: 0.9rem;
    font-weight: 600;
}

.quantity-input {
    height: 35px;
}

.btn-decrease, .btn-increase {
    padding: 0.375rem 0.75rem;
}

.products-category {
    margin-bottom: 2rem;
}

.product-details {
    text-align: center;
    margin-bottom: 1rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Busca de produtos
    const searchInput = document.getElementById('searchProducts');
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        document.querySelectorAll('.product-item').forEach(item => {
            const productName = item.querySelector('.product-name').textContent.toLowerCase();
            item.style.display = productName.includes(searchTerm) ? '' : 'none';
        });
    });

    // Controles de quantidade
    document.querySelectorAll('.btn-decrease').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.parentElement.parentElement.querySelector('.quantity-input');
            if (input.value > 0) {
                input.value = parseInt(input.value) - 1;
                input.dispatchEvent(new Event('change'));
            }
        });
    });

    document.querySelectorAll('.btn-increase').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.parentElement.parentElement.querySelector('.quantity-input');
            input.value = parseInt(input.value) + 1;
            input.dispatchEvent(new Event('change'));
        });
    });

    // Atualizar resumo do pedido
    function updateOrderSummary() {
        const summary = document.getElementById('orderSummary');
        const total = document.getElementById('orderTotal');
        let totalValue = 0;
        let summaryHTML = '';

        document.querySelectorAll('.quantity-input').forEach(input => {
            const quantity = parseInt(input.value);
            if (quantity > 0) {
                const price = parseFloat(input.dataset.price);
                const productName = input.closest('.card').querySelector('.product-name').textContent;
                const itemTotal = quantity * price;
                totalValue += itemTotal;

                summaryHTML += `
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <strong>${productName}</strong><br>
                            <small class="text-muted">${quantity}x R$ ${price.toFixed(2)}</small>
                        </div>
                        <div>R$ ${itemTotal.toFixed(2)}</div>
                    </div>
                `;
            }
        });

        summary.innerHTML = summaryHTML || `
            <div class="alert alert-light">
                Selecione os produtos para criar o pedido
            </div>
        `;
        
        total.textContent = totalValue.toFixed(2).replace('.', ',');
    }

    // Atualizar ao mudar quantidades
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', updateOrderSummary);
    });

    // Validação do formulário
    document.getElementById('orderForm').addEventListener('submit', function(e) {
        const hasProducts = Array.from(document.querySelectorAll('.quantity-input'))
            .some(input => parseInt(input.value) > 0);

        if (!hasProducts) {
            e.preventDefault();
            Swal.fire({
                title: 'Atenção',
                text: 'Selecione pelo menos um produto para criar o pedido',
                icon: 'warning',
                confirmButtonText: 'Ok'
            });
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>