<?php
require_once '../config/config.php';
require_login();
require_admin();

// Lógica para lidar com as ações do formulário
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        try {
            switch ($_POST['action']) {
                case 'add':
                    $image_path = handle_image_upload($_FILES['image']);
                    create_menu_item($_POST['name'], $_POST['description'], $_POST['price'], $_POST['category'], $image_path);
                    $_SESSION['success_message'] = "Item adicionado com sucesso.";
                    break;
                case 'update':
                    $image_path = $_POST['current_image'];
                    if ($_FILES['image']['size'] > 0) {
                        $image_path = handle_image_upload($_FILES['image']);
                    }
                    update_menu_item($_POST['id'], $_POST['name'], $_POST['description'], $_POST['price'], $_POST['category'], $image_path, isset($_POST['is_active']));
                    $_SESSION['success_message'] = "Item atualizado com sucesso.";
                    break;
                case 'delete':
                    delete_menu_item($_POST['id']);
                    $_SESSION['success_message'] = "Item excluído com sucesso.";
                    break;
            }
        } catch (Exception $e) {
            $_SESSION['error_message'] = $e->getMessage();
        }
        header("Location: menus.php");
        exit();
    }
}

// Obter todos os itens do menu
$menuItems = get_all_menu_items();

// Organizar itens por categoria
$menuByCategory = [];
foreach ($menuItems as $item) {
    $menuByCategory[$item['category']][] = $item;
}

include '../includes/header.php';
?>

<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Gestão de Menus</h4>
                <p class="card-description">Adicione, edite ou remova itens do menu</p>
                <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['success_message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success_message']); endif; ?>
                <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['error_message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error_message']); endif; ?>

                <div class="d-flex mb-3">
                    <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal"
                        data-bs-target="#addMenuItemModal">
                        Adicionar Novo Item
                    </button>
                    <button type="button" class="btn btn-secondary"
                        onclick="window.open('gerir_menus/print_menu.php', '_blank')">
                        Imprimir Menu
                    </button>
                </div>

                <div class="row">
                    <?php foreach ($menuByCategory as $category => $items): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5><?php echo htmlspecialchars($category); ?></h5>
                            </div>
                            <div class="card-body">
                                <div class="list-group">
                                    <?php foreach ($items as $item): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center">
                                                <img src="<?php echo htmlspecialchars($item['image_path']); ?>"
                                                    alt="<?php echo htmlspecialchars($item['name']); ?>"
                                                    style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px; margin-right: 10px;">
                                                <div>
                                                    <h6><?php echo htmlspecialchars($item['name']); ?></h6>
                                                    <p class="mb-0">
                                                        <?php echo htmlspecialchars($item['description']); ?></p>
                                                    <small>MZN <?php echo number_format($item['price'], 2); ?></small>
                                                </div>
                                            </div>
                                            <div>
                                                <button class="btn btn-sm btn-info"
                                                    onclick="editMenuItem(<?php echo $item['id']; ?>)">Editar</button>
                                                <button class="btn btn-sm btn-danger"
                                                    onclick="deleteMenuItem(<?php echo $item['id']; ?>)">Excluir</button>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal para adicionar novo item -->
<div class="modal fade" id="addMenuItemModal" tabindex="-1" aria-labelledby="addMenuItemModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addMenuItemModalLabel">Adicionar Novo Item ao Menu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addMenuItemForm" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Descrição</label>
                        <textarea class="form-control" id="description" name="description"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="price" class="form-label">Preço</label>
                        <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label for="category" class="form-label">Categoria</label>
                        <input type="text" class="form-control" id="category" name="category">
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Imagem</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                    </div>
                    <button type="submit" class="btn btn-primary">Adicionar Item</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar item -->
<div class="modal fade" id="editMenuItemModal" tabindex="-1" aria-labelledby="editMenuItemModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editMenuItemModalLabel">Editar Item do Menu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editMenuItemForm" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="edit_id">
                    <input type="hidden" name="current_image" id="edit_current_image">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Descrição</label>
                        <textarea class="form-control" id="edit_description" name="description"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_price" class="form-label">Preço</label>
                        <input type="number" class="form-control" id="edit_price" name="price" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_category" class="form-label">Categoria</label>
                        <input type="text" class="form-control" id="edit_category" name="category">
                    </div>
                    <div class="mb-3">
                        <label for="edit_image" class="form-label">Nova Imagem (opcional)</label>
                        <input type="file" class="form-control" id="edit_image" name="image" accept="image/*">
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="edit_is_active" name="is_active">
                        <label class="form-check-label" for="edit_is_active">Ativo</label>
                    </div>
                    <button type="submit" class="btn btn-primary">Atualizar Item</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function editMenuItem(id) {
    fetch(`gerir_menus/get_menu_item.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_name').value = data.name;
            document.getElementById('edit_description').value = data.description;
            document.getElementById('edit_price').value = data.price;
            document.getElementById('edit_category').value = data.category;
            document.getElementById('edit_current_image').value = data.image_path;
            document.getElementById('edit_is_active').checked = data.is_active == 1;
            $('#editMenuItemModal').modal('show');
        })
        .catch(error => console.error('Error:', error));
}

function deleteMenuItem(id) {
    if (confirm('Tem certeza que deseja excluir este item?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="${id}">
                `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
<?php include '../includes/footer.php'; ?>