<?php
require_once '../config/config.php';
require_login();

$users = get_all_users();
include '../includes/header.php';
?>

<div class="row">
    <div class="col-sm-12">
        <div class="home-tab">
            <div class="d-sm-flex align-items-center justify-content-between border-bottom">
                <h4 class="card-title">Usuários</h4>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">Adicionar
                    Usuário</button>
            </div>
            <div class="row mt-4">
                <div class="col-12">
                    <div class="table-responsive">
                        <table class="table" id="userTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>Username</th>
                                    <th>Role</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td><?php echo $user['name']; ?></td>
                                    <td><?php echo $user['username']; ?></td>
                                    <td><?php echo $user['role']; ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary editUser"  data-bs-toggle="modal" data-bs-target="#editUserModal"
                                            data-id="<?php echo $user['id']; ?>">Editar</button>
                                        <button class="btn btn-sm btn-danger deleteUser"
                                            data-id="<?php echo $user['id']; ?>">Excluir</button>
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
</div>

<!-- Modal para Adicionar Usuário -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">Adicionar Usuário</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addUserForm">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Senha</label>
                        <input type="password" class="form-control" id="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Papel</label>
                        <select class="form-select" id="role" required>
                            <option value="admin">Admin</option>
                            <option value="manager">Gerente</option>
                            <option value="waiter">Garçom</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="addUserButton">Adicionar Usuário</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar usuário -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Editar Usuário</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editUserForm">
                    <input type="hidden" id="userId">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Senha</label>
                        <input type="password" class="form-control" id="password"
                            placeholder="Deixe em branco para não alterar">
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Papel</label>
                        <select class="form-select" id="role" required>
                            <option value="admin">Admin</option>
                            <option value="manager">Gerente</option>
                            <option value="waiter">Garçom</option>
                        </select>
                    </div>
                    <button type="button" class="btn btn-primary" id="updateUserButton">Atualizar Usuário</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.getElementById('addUserButton').addEventListener('click', function() {
    const username = document.getElementById('username').value;
    const name = document.getElementById('name').value;
    const password = document.getElementById('password').value;
    const role = document.getElementById('role').value;

    fetch('gerir_users/get_users.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                username,
                name,
                password,
                role
            }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire('Sucesso!', 'Usuário adicionado com sucesso!', 'success');
                location.reload(); // Atualiza a página
            } else {
                Swal.fire('Erro!', 'Não foi possível adicionar o usuário.', 'error');
            }
        });
});

// Event listeners for edit and delete buttons

document.querySelectorAll('.editUserButton').forEach(button => {
    button.addEventListener('click', function() {
        const id = this.getAttribute('data-id');

        // Buscar dados do usuário
        fetch('gerir_users/edit_user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    user_id: id
                }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Preencher o formulário do modal com os dados do usuário
                    document.getElementById('userId').value = data.user.id;
                    document.getElementById('username').value = data.user.username;
                    document.getElementById('name').value = data.user.name;
                    document.getElementById('role').value = data.user.role;

                    // Mostrar o modal
                    const editUserModal = new bootstrap.Modal(document.getElementById(
                        'editUserModal'));
                    editUserModal.show();
                } else {
                    Swal.fire('Erro!', 'Não foi possível buscar os dados do usuário.', 'error');
                }
            });
    });
});

document.getElementById('updateUserButton').addEventListener('click', function() {
    const id = document.getElementById('userId').value;
    const username = document.getElementById('username').value;
    const name = document.getElementById('name').value;
    const password = document.getElementById('password').value; // pode ser deixado em branco
    const role = document.getElementById('role').value;

    fetch('gerir_users/edit_user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                id,
                username,
                name,
                password,
                role
            }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire('Sucesso!', 'Usuário atualizado com sucesso!', 'success')
                    .then(() => {
                        window.location.reload(); // Atualiza a página para mostrar as alterações
                    });
            } else {
                Swal.fire('Erro!', 'Não foi possível atualizar o usuário.', 'error');
            }
        });
});

document.querySelectorAll('.deleteUser').forEach(button => {
    button.addEventListener('click', function() {
        const userId = this.getAttribute('data-id');
        Swal.fire({
            title: 'Tem certeza?',
            text: 'Você não poderá reverter isso!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sim, excluir!'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`gerir_users/delete_user.php?id=${userId}`, {
                        method: 'DELETE'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Excluído!', 'Usuário excluído com sucesso!',
                                'success');
                            location.reload(); // Atualiza a página
                        } else {
                            Swal.fire('Erro!', 'Não foi possível excluir o usuário.',
                                'error');
                        }
                    });
            }
        });
    });
});
</script>