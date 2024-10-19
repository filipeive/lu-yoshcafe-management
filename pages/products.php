<?php
require_once '../config/config.php';
require_login();

// Configurações de paginação
$limit = 6; // Limite de produtos por página
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filtros
$categoryFilter = isset($_GET['category']) ? $_GET['category'] : '';
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';


$products = get_filtered_products($categoryFilter, $searchTerm, $limit, $offset);
$totalProducts = count_filtered_products($categoryFilter, $searchTerm);
$totalPages = ceil($totalProducts / $limit);

$categories = get_all_categories();

include '../includes/header.php'; 
?>

<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Gestão de Estoque</h4>

                <div class="mb-3">
                    <form class="d-flex" method="GET" action="">
                        <input type="text" class="form-control me-2" name="search" placeholder="Pesquisar produto"
                            value="<?php echo htmlspecialchars($searchTerm); ?>">
                        <select class="form-select me-2" name="category">
                            <option value="">Todas as Categorias</option>
                            <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>"
                                <?php echo $category['id'] == $categoryFilter ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                    </form>
                </div>

                <div class="row">
                    <?php if (count($products) > 0): ?>
                    <?php foreach ($products as $product): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                <h6 class="card-subtitle mb-2 text-muted">
                                    <?php echo htmlspecialchars($product['category_name']); ?></h6>
                                <p class="card-text">Preço: MZN
                                    <?php echo number_format($product['price'], 2, ',', '.'); ?></p>
                                <p class="card-text">Estoque: <?php echo $product['stock_quantity']; ?></p>
                                <div class="d-flex justify-content-between">
                                    <button class="btn btn-warning btn-sm"
                                        onclick="editProduct(<?php echo $product['id']; ?>)">Editar</button>
                                    <button class="btn btn-danger btn-sm"
                                        onclick="deleteProduct(<?php echo $product['id']; ?>)">Excluir</button>
                                    <button class="btn btn-info btn-sm"
                                        onclick="updateStock(<?php echo $product['id']; ?>)">Atualizar Estoque</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <p>Nenhum produto encontrado.</p>
                    <?php endif; ?>
                </div>

                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link"
                                href="?page=<?php echo $i; ?>&category=<?php echo htmlspecialchars($categoryFilter); ?>&search=<?php echo htmlspecialchars($searchTerm); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>

            </div>
        </div>
    </div>
</div>
<!-- Modais -->
<!-- Modal Add Product -->
<div class="modal fade" id="addProductModal" tabindex="-1" role="dialog" aria-labelledby="addProductModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addProductModalLabel">Adicionar Novo Produto</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addProductForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="product_name">Nome do Produto</label>
                        <input type="text" class="form-control" id="product_name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="product_description">Descrição</label>
                        <textarea class="form-control" id="product_description" name="description"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="product_price">Preço</label>
                        <input type="number" class="form-control" id="product_price" name="price" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="product_stock_quantity">Quantidade em Estoque</label>
                        <input type="number" class="form-control" id="product_stock_quantity" name="stock_quantity"
                            required>
                    </div>
                    <div class="form-group">
                        <label for="product_category_id">Categoria</label>
                        <select class="form-control" id="product_category_id" name="category_id" required>
                            <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>">
                                <?php echo htmlspecialchars($category['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!--Modal edit product -->
<div class="modal fade" id="editProductModal" tabindex="-1" role="dialog" aria-labelledby="editProductModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProductModalLabel">Editar Produto</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editProductForm">
                <div class="modal-body">
                    <input type="hidden" id="edit_product_id" name="id">
                    <div class="form-group">
                        <label for="edit_product_name">Nome do Produto</label>
                        <input type="text" class="form-control" id="edit_product_name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_product_description">Descrição</label>
                        <textarea class="form-control" id="edit_product_description" name="description"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit_product_price">Preço</label>
                        <input type="number" class="form-control" id="edit_product_price" name="price" step="0.01"
                            required>
                    </div>
                    <div class="form-group">
                        <label for="edit_product_stock_quantity">Quantidade em Estoque</label>
                        <input type="number" class="form-control" id="edit_product_stock_quantity" name="stock_quantity"
                            required>
                    </div>
                    <div class="form-group">
                        <label for="edit_product_category_id">Categoria</label>
                        <select class="form-control" id="edit_product_category_id" name="category_id" required>
                            <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>">
                                <?php echo htmlspecialchars($category['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- /.modal-Categoria -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog" aria-labelledby="addCategoryModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCategoryModalLabel">Adicionar Nova Categoria</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addCategoryForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="category_name">Nome da Categoria</label>
                        <input type="text" class="form-control" id="category_name" name="name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>
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