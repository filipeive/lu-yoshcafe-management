<?php
require_once '../config/config.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $username = $_POST['username'];
    $name = $_POST['name'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Atualiza o usuário
    if (update_user($id, $username, $password, $name, $role)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}

$id = $_GET['id'];
$user = get_user_by_id($id);
if (!$user) {
    echo "Usuário não encontrado!";
    exit;
}

include '../includes/header.php';
?>

<div class="container">
    <h2>Editar Usuário</h2>
    <form id="editUserForm">
        <input type="hidden" id="userId" value="<?php echo $user['id']; ?>">
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" value="<?php echo $user['username']; ?>" required>
        </div>
        <div class="mb-3">
            <label for="name" class="form-label">Nome</label>
            <input type="text" class="form-control" id="name" value="<?php echo $user['name']; ?>" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Senha</label>
            <input type="password" class="form-control" id="password" placeholder="Deixe em branco para não alterar">
        </div>
        <div class="mb-3">
            <label for="role" class="form-label">Papel</label>
            <select class="form-select" id="role" required>
                <option value="admin" <?php if ($user['role'] == 'admin') echo 'selected'; ?>>Admin</option>
                <option value="manager" <?php if ($user['role'] == 'manager') echo 'selected'; ?>>Gerente</option>
                <option value="waiter" <?php if ($user['role'] == 'waiter') echo 'selected'; ?>>Garçom</option>
            </select>
        </div>
        <button type="button" class="btn btn-primary" id="updateUserButton">Atualizar Usuário</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.getElementById('updateUserButton').addEventListener('click', function() {
    const id = document.getElementById('userId').value;
    const username = document.getElementById('username').value;
    const name = document.getElementById('name').value;
    const password = document.getElementById('password').value; // pode ser deixado em branco
    const role = document.getElementById('role').value;

    fetch('edit_user.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({ id, username, name, password, role }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Sucesso!', 'Usuário atualizado com sucesso!', 'success')
                .then(() => {
                    window.location.href = 'users.php'; // Redireciona de volta para a lista de usuários
                });
        } else {
            Swal.fire('Erro!', 'Não foi possível atualizar o usuário.', 'error');
        }
    });
});
</script>
