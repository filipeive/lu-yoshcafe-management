<?php
require_once '../config/config.php';
//require_once '../functions/menu_functions.php';
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

include '../includes/header.php';
?>

<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Gestão de Menus</h4>
                <p class="card-description">
                    Adicione, edite ou remova itens do menu
                </p>
                <?php
                        if (isset($_SESSION['success_message'])) {
                            echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
                            unset($_SESSION['success_message']);
                        }
                        if (isset($_SESSION['error_message'])) {
                            echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
                            unset($_SESSION['error_message']);
                        }
                        ?>
                <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal"
                    data-bs-target="#addMenuItemModal">
                    Adicionar Novo Item
                </button>
                <!-- Adicione este botão logo após o botão "Adicionar Novo Item" -->
                <button type="button" class="btn btn-secondary mb-3 ms-2"
                    onclick="window.open('gerir_menus/print_menu.php', '_blank')">
                    Imprimir Menu
                </button>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Imagem</th>
                                <th>Nome</th>
                                <th>Descrição</th>
                                <th>Preço</th>
                                <th>Categoria</th>
                                <th>Ativo</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($menuItems as $item): ?>
                            <tr>
                                <td><img src="<?php echo htmlspecialchars($item['image_path']); ?>"
                                        alt="<?php echo htmlspecialchars($item['name']); ?>"
                                        style="width: 50px; height: 50px; object-fit: cover;"></td>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td><?php echo htmlspecialchars($item['description']); ?></td>
                                <td>MZN <?php echo number_format($item['price'], 2); ?></td>
                                <td><?php echo htmlspecialchars($item['category']); ?></td>
                                <td><?php echo $item['is_active'] ? 'Sim' : 'Não'; ?></td>
                                <td>
                                    <button class="btn btn-sm btn-info"
                                        onclick="editMenuItem(<?php echo $item['id']; ?>)">Editar</button>
                                    <button class="btn btn-sm btn-danger"
                                        onclick="deleteMenuItem(<?php echo $item['id']; ?>)">Excluir</button>
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