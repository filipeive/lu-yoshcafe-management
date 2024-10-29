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
</style>

<div class="content-wrapper">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-0">Gerenciar Produtos</h4><br>
                    <div class="d-flex justify-content-space-around align-items-center mb-4">
                        <button type="button" class="btn btn-primary btn-sm btn-icon-text" data-toggle="modal"
                            data-target="#addProductModal">
                            <i class="ti-plus btn-icon-prepend"></i>
                            Adicionar Produto
                        </button>
                        <button type="button" class="btn btn-warning btn-sm btn-icon-text" data-toggle="modal"
                            data-target="#addCategoryModal">
                            <i class="ti-plus btn-icon-prepend"></i>
                            Adicionar Nova Categoria
                        </button>
                    </div>

                    <!-- Filtros em Card Separado -->
                    <div class="card bg-light mb-4">
                        <div class="card-body py-3">
                            <form class="row g-3" method="GET" action="">
                                <div class="col-md-5">
                                    <div class="input-group">
                                        <span class="input-group-text bg-primary text-white">
                                            <i class="ti-search"></i>
                                        </span>
                                        <input type="text" class="form-control" name="search"
                                            placeholder="Pesquisar produto"
                                            value="<?php echo htmlspecialchars($searchTerm); ?>">
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="input-group">
                                        <span class="input-group-text bg-primary text-white">
                                            <i class="ti-filter"></i>
                                        </span>
                                        <select class="form-select" name="category">
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
                                    <button type="submit" class="btn btn-primary w-100">
                                        Filtrar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Grid de Produtos -->
                    <div class="row">
                        <?php if (count($products) > 0): ?>
                        <?php foreach ($products as $product): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 product-card">
                                <div class="position-relative">
                                    <?php if ($product['image_path'] || $product['image_path']): ?>
                                    <img src="<?php echo htmlspecialchars('../uploads/' . 
                                                ($product['image_path'] ? 'products/' . $product['image_path'] : 
                                                'menu/' . $product['image_path'])); ?>" class="card-img-top"
                                        alt="<?php echo htmlspecialchars($product['name']); ?>"
                                        style="height: 200px; object-fit: cover;">
                                    <?php else: ?>
                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center"
                                        style="height: 200px;">
                                        <i class="ti-image text-muted" style="font-size: 3rem;"></i>
                                    </div>
                                    <?php endif; ?>
                                    <div class="category-badge">
                                        <span class="badge bg-primary">
                                            <?php echo htmlspecialchars($product['category_name']); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title text-primary">
                                        <?php echo htmlspecialchars($product['name']); ?></h5>
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div class="price-tag">
                                            <span class="text-muted">Preço:</span>
                                            <span class="h5 mb-0 text-success">
                                                MZN <?php echo number_format($product['price'], 2, ',', '.'); ?>
                                            </span>
                                        </div>
                                        <div class="stock-info">
                                            <span class="badge <?php echo $product['stock_quantity'] > 10 ? 'bg-success' : 
                                                    ($product['stock_quantity'] > 5 ? 'bg-warning' : 'bg-danger'); ?>">
                                                Estoque: <?php echo $product['stock_quantity']; ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="btn-group w-100" role="group">
                                        <button class="btn btn-outline-warning btn-sm"
                                            onclick="editProduct(<?php echo $product['id']; ?>)">
                                            <i class="ti-pencil"></i> Editar
                                        </button>
                                        <button class="btn btn-outline-info btn-sm"
                                            onclick="updateStock(<?php echo $product['id']; ?>)">
                                            <i class="ti-package"></i> Estoque
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm"
                                            onclick="deleteProduct(<?php echo $product['id']; ?>)">
                                            <i class="ti-trash"></i> Excluir
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-info text-center" role="alert">
                                <i class="ti-info-circle me-2"></i> Nenhum produto encontrado.
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Paginação -->
                    <?php if ($totalPages > 1): ?>
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link"
                                    href="?page=<?php echo $i; ?>&category=<?php echo 
                                    htmlspecialchars($categoryFilter); ?>&search=<?php echo htmlspecialchars($searchTerm); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
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
                        showNotification('Erro', 'Erro ao excluir o produto.', 'error');
                    }
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
/*
// Evento de submissão do formulário de adição de produto
$('#addProductForm').on('submit', function(e) {
    e.preventDefault();
    $.ajax({
        url: 'gerir_stoque/add_product.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showNotification('Sucesso', 'Produto adicionado com sucesso!', 'success');
                location.reload();
            } else {
                showNotification('Erro', 'Erro ao adicionar o produto.', 'error');
            }
        }
    });
});

// Evento de submissão do formulário de edição de produto
$('#editProductForm').on('submit', function(e) {
    e.preventDefault();
    $.ajax({
        url: 'gerir_stoque/edit_product.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showNotification('Sucesso', 'Produto atualizado com sucesso!', 'success');
                location.reload();
            } else {
                showNotification('Erro', 'Erro ao atualizar o produto.', 'error');
            }
        }
    });
});
*/
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
</script>

<?php include '../includes/footer.php'; ?>