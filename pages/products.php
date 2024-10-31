<?php
require_once '../config/config.php';
require_login();

$limit = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$categoryFilter = isset($_GET['category']) ? $_GET['category'] : '';
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

$products = get_filtered_products($categoryFilter, $searchTerm, $limit, $offset);
$totalProducts = count_filtered_products($categoryFilter, $searchTerm);
$totalPages = ceil($totalProducts / $limit);

$categories = get_all_categories();

include '../includes/header.php'; 
?>

<!-- Adicione este CSS no seu arquivo de estilos ou no head da página -->
<style>
.product-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border: none;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.category-badge {
    position: absolute;
    top: 10px;
    right: 10px;
}

.btn-group .btn {
    flex: 1;
    padding: 0.375rem;
}

.price-tag {
    display: flex;
    flex-direction: column;
}

.stock-info {
    font-size: 0.9rem;
}

.pagination {
    margin-bottom: 0;
}

.input-group-text {
    border: none;
}

.form-control:focus,
.form-select:focus {
    border-color: #4B49AC;
    box-shadow: 0 0 0 0.2rem rgba(75, 73, 172, 0.25);
}

/* Estilos personalizados */
.card {
    border-radius: 0.5rem;
    transition: all 0.2s ease;
}

.shadow-hover:hover {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
}

.product-card {
    border: 1px solid rgba(0,0,0,.125);
}

.product-card:hover {
    border-color: #0d6efd;
}

.badge-stock {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    color: white;
}

.btn-group .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.btn-group .btn i {
    font-size: 0.875rem;
}

.page-link {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.input-group-text {
    padding: 0.25rem 0.5rem;
}

.form-control-sm, .form-select-sm {
    font-size: 0.875rem;
}

.alert {
    border-radius: 0.375rem;
}

.badge {
    font-weight: normal;
}
</style>
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <!-- Header Section -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h4 class="card-title d-flex align-items-center gap-2 mb-1">
                            <i class="ti-package text-primary"></i>
                            Gerenciamento de Produtos
                        </h4>
                        <p class="text-muted small mb-0">Total: <?php echo count($products); ?> produtos</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary btn-sm d-flex align-items-center gap-1"
                            data-toggle="modal" data-target="#addProductModal">
                            <i class="ti-plus"></i>
                            <span>Produto</span>
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1"
                            data-toggle="modal" data-target="#addCategoryModal">
                            <i class="ti-tag"></i>
                            <span>Categoria</span>
                        </button>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="card bg-light border-0 rounded-3 mb-3">
                    <div class="card-body py-2">
                        <form class="row g-2" method="GET" action="">
                            <div class="col-md-5">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text border-0 bg-transparent">
                                        <i class="ti-search text-muted"></i>
                                    </span>
                                    <input type="text" class="form-control form-control-sm border-0" name="search"
                                        placeholder="Buscar produtos..."
                                        value="<?php echo htmlspecialchars($searchTerm); ?>">
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text border-0 bg-transparent">
                                        <i class="ti-layers text-muted"></i>
                                    </span>
                                    <select class="form-select form-select-sm border-0" name="category">
                                        <option value="">Todas as Categorias</option>
                                        <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>"
                                            <?php echo $category['id'] == $categoryFilter ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary btn-sm w-100">
                                    <i class="ti-filter me-1"></i>Filtrar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Grid de Produtos -->
                <div class="row g-2">
                    <?php if (count($products) > 0): ?>
                    <?php foreach ($products as $product): ?>
                    <div class="col-6 col-md-4 col-lg-4">
                        <div class="card product-card h-100 border shadow-hover">
                            <div class="position-relative">
                                <?php if ($product['image_path'] || $product['image_path']): ?>
                                <img src="<?php echo htmlspecialchars('../uploads/' . 
                                                    ($product['image_path'] ? 'products/' . $product['image_path'] : 
                                                    'menu/' . $product['image_path'])); ?>" class="card-img-top"
                                    alt="<?php echo htmlspecialchars($product['name']); ?>"
                                    style="height: 120px; object-fit: cover;">
                                <?php else: ?>
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center"
                                    style="height: 120px;">
                                    <i class="ti-image text-muted" style="font-size: 2rem;"></i>
                                </div>
                                <?php endif; ?>

                                <!-- Stock Badge -->
                                <div class="position-absolute top-0 end-0 m-1">
                                    <span class="badge-stock <?php echo $product['stock_quantity'] > 10 ? 'bg-success' : 
                                                    ($product['stock_quantity'] > 5 ? 'bg-warning' : 'bg-danger'); ?>">
                                        <?php echo $product['stock_quantity']; ?>
                                    </span>
                                </div>
                            </div>

                            <div class="card-body p-2">
                                <!-- Category Badge -->
                                <span class="badge bg-primary bg-opacity-10 text-primary mb-1 rounded-pill">
                                    <i class="ti-tag me-1"></i>
                                    <?php echo htmlspecialchars($product['category_name']); ?>
                                </span>

                                <h6 class="card-title mb-2 text-truncate"
                                    title="<?php echo htmlspecialchars($product['name']); ?>">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </h6>

                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold text-success">
                                        MZN <?php echo number_format($product['price'], 2, ',', '.'); ?>
                                    </span>
                                </div>

                                <div class="btn-group btn-group-sm w-100">
                                    <button class="btn btn-outline-secondary btn-sm"
                                        onclick="editProduct(<?php echo $product['id']; ?>)" title="Editar">
                                        <i class="ti-pencil"></i>
                                    </button>
                                    <button class="btn btn-outline-secondary btn-sm"
                                        onclick="updateStock(<?php echo $product['id']; ?>)" title="Atualizar Estoque">
                                        <i class="ti-package"></i>
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm"
                                        onclick="deleteProduct(<?php echo $product['id']; ?>)" title="Excluir">
                                        <i class="ti-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info d-flex align-items-center py-2" role="alert">
                            <i class="ti-info-circle me-2"></i>
                            <small>Nenhum produto encontrado. Ajuste os filtros ou adicione novos produtos.</small>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Paginação -->
                <?php if ($totalPages > 1): ?>
                <nav aria-label="Navegação" class="mt-3">
                    <ul class="pagination pagination-sm justify-content-center mb-0">
                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link"
                                href="?page=<?php echo $page-1; ?>&category=<?php echo 
                                    htmlspecialchars($categoryFilter); ?>&search=<?php echo htmlspecialchars($searchTerm); ?>">
                                <i class="ti-angle-left"></i>
                            </a>
                        </li>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link"
                                href="?page=<?php echo $i; ?>&category=<?php echo 
                                    htmlspecialchars($categoryFilter); ?>&search=<?php echo htmlspecialchars($searchTerm); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>

                        <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                            <a class="page-link"
                                href="?page=<?php echo $page+1; ?>&category=<?php echo 
                                    htmlspecialchars($categoryFilter); ?>&search=<?php echo htmlspecialchars($searchTerm); ?>">
                                <i class="ti-angle-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'modais/modais_produtos.php'?>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
// Função para mostrar notificações
function showNotification(title, message, type) {
    Swal.fire({
        title: title,
        text: message,
        icon: type,
        timer: 2000,
        showConfirmButton: false
    });
}

// Função para editar produto
function editProduct(productId) {
    fetch('gerir_stoque/get_product.php?id=' + productId)
        .then(response => response.json())
        .then(data => {
            document.getElementById('edit_product_id').value = data.id;
            document.getElementById('edit_product_name').value = data.name;
            document.getElementById('edit_product_description').value = data.description;
            document.getElementById('edit_product_price').value = data.price;
            document.getElementById('edit_product_stock_quantity').value = data.stock_quantity;
            document.getElementById('edit_product_category_id').value = data.category_id;
            $('#editProductModal').modal('show');
        })
        .catch(error => {
            showNotification('Erro', 'Não foi possível carregar os dados do produto', 'error');
        });
}

// Função para deletar produto
function deleteProduct(productId) {
    Swal.fire({
        title: 'Tem certeza?',
        text: "Você não poderá reverter esta ação!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('gerir_stoque/delete_product.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id=' + productId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Sucesso', 'Produto excluído com sucesso!', 'success');
                    location.reload();
                } else {
                    showNotification('Erro', data.message || 'Erro ao excluir o produto.', 'error');
                }
            })
            .catch(error => {
                showNotification('Erro', 'Erro de conexão ao excluir o produto.', 'error');
            });
        }
    });
}


// Função para atualizar estoque
function updateStock(productId) {
    Swal.fire({
        title: 'Atualizar Estoque',
        input: 'number',
        inputLabel: 'Digite a quantidade a ser adicionada ou removida do estoque:',
        inputPlaceholder: 'Quantidade',
        showCancelButton: true,
        confirmButtonText: 'Atualizar',
        cancelButtonText: 'Cancelar',
        inputValidator: (value) => {
            if (!value) {
                return 'Você precisa inserir um número!';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('gerir_stoque/update_stock.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'product_id=' + productId + '&quantity=' + result.value
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Sucesso', 'Estoque atualizado com sucesso!', 'success');
                        location.reload();
                    } else {
                        showNotification('Erro', 'Erro ao atualizar o estoque.', 'error');
                    }
                });
        }
    });
}

// Função para manipular o envio do formulário de adicionar produto
$('#addProductForm').on('submit', function(e) {
    e.preventDefault();

    var formData = new FormData(this);

    // Adiciona o arquivo de imagem ao FormData
    var imageFile = $('#product_image')[0].files[0];
    if (imageFile) {
        formData.append('product_image', imageFile);
    }

    $.ajax({
        url: 'gerir_stoque/add_product.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showNotification('Sucesso', 'Produto adicionado com sucesso!', 'success');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                showNotification('Erro', response.message || 'Erro ao adicionar o produto.',
                    'error');
            }
        },
        error: function(xhr, status, error) {
            showNotification('Erro', 'Erro ao processar a requisição.', 'error');
            console.error(error);
        }
    });
});

// Função para manipular o envio do formulário de editar produto
$('#editProductForm').on('submit', function(e) {
    e.preventDefault();

    var formData = new FormData(this);

    // Adiciona o arquivo de imagem ao FormData
    var imageFile = $('#edit_product_image')[0].files[0];
    if (imageFile) {
        formData.append('product_image', imageFile);
    }

    $.ajax({
        url: 'gerir_stoque/edit_product.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showNotification('Sucesso', 'Produto atualizado com sucesso!', 'success');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                showNotification('Erro', response.message || 'Erro ao atualizar o produto.',
                    'error');
            }
        },
        error: function(xhr, status, error) {
            showNotification('Erro', 'Erro ao processar a requisição.', 'error');
            console.error(error);
        }
    });
});

// Preview da imagem antes do upload
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function(e) {
            $('#' + previewId).attr('src', e.target.result).show();
            $('#no_image_text').hide();
        }

        reader.readAsDataURL(input.files[0]);
    }
}

$('#product_image').change(function() {
    previewImage(this, 'product_image_preview');
});

$('#edit_product_image').change(function() {
    previewImage(this, 'current_product_image');
});
// Evento de submissão do formulário de adição de categoria
$('#addCategoryForm').on('submit', function(e) {
    e.preventDefault();
    $.ajax({
        url: 'gerir_stoque/add_category.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showNotification('Sucesso', 'Categoria adicionada com sucesso!', 'success');
                location.reload();
            } else {
                showNotification('Erro', 'Erro ao adicionar a categoria.', 'error');
            }
        }
    });
});
document.getElementById('search-input').addEventListener('keyup', function() {
    const searchTerm = this.value;
    const categoryFilter = ""; // Inclua o filtro de categoria, se necessário

    if (searchTerm.length > 1) {
        fetch(`products.php?search=${encodeURIComponent(searchTerm)}&category=${categoryFilter}&ajax=true`)
            .then(response => response.json())
            .then(data => {
                const searchResults = document.getElementById('search-results');
                searchResults.innerHTML = '';

                if (data.length > 0) {
                    data.forEach(product => {
                        const item = document.createElement('div');
                        item.textContent = product.name;
                        item.className = 'search-result-item';
                        searchResults.appendChild(item);
                    });
                    searchResults.style.display = 'block';
                } else {
                    searchResults.style.display = 'none';
                }
            })
            .catch(error => console.error('Erro:', error));
    } else {
        document.getElementById('search-results').style.display = 'none';
    }
});

</script>

<?php include '../includes/footer.php'; ?>
</div>