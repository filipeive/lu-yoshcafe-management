<?php
require_once '../config/config.php';
require_login();

$pageTitle = "POS - Sistema de Vendas";
include '../includes/header.php';

$categories = get_all_categories();
$categoryFilter = isset($_GET['category']) ? $_GET['category'] : null;
$searchTerm = isset($_GET['search']) ? $_GET['search'] : null;
$category_id = $categoryFilter;
$totalProducts = count_filtered_products($categoryFilter, $searchTerm);
$limit = 12; // Aumentado para melhor grid layout
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$products = get_filtered_products($categoryFilter, $searchTerm, $limit, $offset);
//$products_image = get_products_image($product_id, $products);

?>

<head>
    <link rel="stylesheet" href="assets/pos.css">
</head>
<div class="pos-wrapper">
    <div class="row">
        <!-- Área de Produtos -->
        <div class="col-lg-8">
            <div class="mb-4">
                <div class="d-flex gap-3 align-items-center">
                    <div class="flex-grow-1">
                        <input type="text" class="form-control form-control-lg" placeholder="Pesquisar produtos..."
                            id="searchInput" onkeyup="filterProducts()">
                    </div>
                    <select id="categorySelect" class="form-select form-select-lg" style="width: auto;"
                        onchange="filterProducts()">
                        <option value="">Todas as Categorias</option>
                        <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="category-filters">
                <button class="category-btn active" data-category="all">Todos</button>
                <?php foreach ($categories as $category): ?>
                <button class="category-btn" data-category="<?php echo $category['id']; ?>">
                    <?php echo htmlspecialchars($category['name']); ?>
                </button>
                <?php endforeach; ?>
            </div>

            <div class="row g-3" id="productsGrid">
                <?php foreach ($products as $product): ?>
                <div class="col-md-3 product-item" data-category="<?php echo $product['category_id']; ?>">
                    <div class="product-card"
                        onclick="addToCart(<?php echo htmlspecialchars(json_encode($product)); ?>)">
                        <img src="<?php echo $product['image_path'] ?: '../public/assets/images/restaurant-bg.jpg'; ?>"
                            class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                            <p class="price">MZN <?php echo number_format($product['price'], 2); ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Área do Carrinho -->
        <div class="col-lg-4">
            <div class="cart-wrapper">
                <div class="cart-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Pedido Atual</h4>
                        <button class="btn btn-outline-danger btn-sm" onclick="resetSale()">
                            <i class="mdi mdi-delete"></i> Limpar
                        </button>
                    </div>
                </div>

                <div class="cart-items" id="cartItems">
                    <!-- Items serão adicionados via JavaScript -->
                </div>

                <div class="p-3 border-top">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal:</span>
                        <span id="subtotal" class="font-weight-medium">MZN 0.00</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="h5">Total:</span>
                        <span id="total" class="h5">MZN 0.00</span>
                    </div>
                </div>

                <!-- Métodos de Pagamento -->
                <div class="payment-methods">
                    <h5 class="mb-3">Método de Pagamento</h5>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="payment-card" onclick="selectPayment('cash')">
                                <img src="../public/images/payment/cash.png" alt="Dinheiro">
                                <h6 class="mb-2">Dinheiro</h6>
                                <input type="number" class="form-control form-control-sm" id="cashAmount"
                                    placeholder="0.00" onchange="calculateChange()">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="payment-card" onclick="selectPayment('card')">
                                <img src="../public/images/payment/pos.png" alt="Cartão">
                                <h6 class="mb-2">Cartão</h6>
                                <input type="number" class="form-control form-control-sm" id="cardAmount"
                                    placeholder="0.00" onchange="calculateChange()">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="payment-card" onclick="selectPayment('mpesa')">
                                <img src="../public/images/payment/M-Pesa.jpg" alt="M-Pesa">
                                <h6 class="mb-2">M-Pesa</h6>
                                <input type="number" class="form-control form-control-sm" id="mpesaAmount"
                                    placeholder="0.00" onchange="calculateChange()">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="payment-card" onclick="selectPayment('emola')">
                                <img src="../public/images/payment/emola.png" alt="E-mola">
                                <h6 class="mb-2">E-mola</h6>
                                <input type="number" class="form-control form-control-sm" id="emolaAmount"
                                    placeholder="0.00" onchange="calculateChange()">
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label">Troco:</label>
                        <input type="text" class="form-control" id="changeAmount" readonly>
                    </div>
                </div>

                <div class="action-buttons p-3">
                    <button id="btnFinalizeOrder" class="btn-finalize" onclick="processSale()">
                        <i class="mdi mdi-check-circle"></i> Finalizar Pedido
                    </button>

                    <button class="btn-preview" onclick="previewReceipt()">
                        <i class="mdi mdi-printer"></i> Pré-visualizar Recibo
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="assets/pos.js"></script>
<?php include '../includes/footer.php'; ?>