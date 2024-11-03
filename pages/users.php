<?php
require_once '../config/config.php';
require_login();

$users = get_all_users();
include '../includes/header.php';
?>

<!-- Adicione este CSS personalizado -->
<style>
.avatar {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    position: relative;
}

.avatar-md {
    width: 40px;
    height: 40px;
}

.avatar-xl {
    width: 100px;
    height: 100px;
}

.avatar-initial {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: 500;
}

.table td {
    vertical-align: middle;
}

.dropdown-item {
    padding: 0.5rem 1rem;
}

.dropdown-item i {
    width: 16px;
    height: 16px;
}

.icon-sm {
    width: 16px;
    height: 16px;
}

.icon-md {
    width: 24px;
    height: 24px;
}
</style>
<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="card-title mb-0">
                            <i data-feather="users" class="icon-md me-2"></i>
                            Gerenciamento de Usuários
                        </h4>
                        <p class="text-muted mb-0">Gerencie todos os usuários do sistema</p>
                    </div>
                    <button class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal"
                        data-bs-target="#addUserModal">
                        <i data-feather="user-plus" class="icon-sm me-2"></i>
                        Adicionar Usuário
                    </button>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive rounded">
                            <table class="table table-hover" id="userTable">
                                <thead class="bg-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Usuário</th>
                                        <th>Função</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo $user['id']; ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-md me-3">
                                                    <div class="avatar-initial rounded-circle bg-primary">
                                                        <?php echo strtoupper(substr($user['name'], 0, 2)); ?>
                                                    </div>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0"><?php echo $user['name']; ?></h6>
                                                    <small class="text-muted">@<?php echo $user['username']; ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php
                                            $roleClasses = [
                                                'admin' => 'badge bg-danger',
                                                'manager' => 'badge bg-warning',
                                                'waiter' => 'badge bg-info'
                                            ];
                                            $roleIcons = [
                                                'admin' => 'shield',
                                                'manager' => 'briefcase',
                                                'waiter' => 'coffee'
                                            ];
                                            $roleNames = [
                                                'admin' => 'Administrador',
                                                'manager' => 'Gerente',
                                                'waiter' => 'Garçom'
                                            ];
                                            ?>
                                            <span class="<?php echo $roleClasses[$user['role']]; ?>">
                                                <i data-feather="<?php echo $roleIcons[$user['role']]; ?>"
                                                    class="icon-xs me-1"></i>
                                                <?php echo $roleNames[$user['role']]; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">Ativo</span>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-link p-0" type="button"
                                                    id="actionMenu<?php echo $user['id']; ?>" data-bs-toggle="dropdown"
                                                    aria-expanded="false">
                                                    <i data-feather="more-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu"
                                                    aria-labelledby="actionMenu<?php echo $user['id']; ?>">
                                                    <li>
                                                        <a class="dropdown-item d-flex align-items-center viewProfile"
                                                            href="#" data-id="<?php echo $user['id']; ?>"
                                                            data-bs-toggle="modal" data-bs-target="#viewProfileModal">
                                                            <i data-feather="user" class="icon-sm me-2"></i>
                                                            Ver Perfil
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item d-flex align-items-center editUser"
                                                            href="#" data-id="<?php echo $user['id']; ?>"
                                                            data-bs-toggle="modal" data-bs-target="#editUserModal">
                                                            <i data-feather="edit-2" class="icon-sm me-2"></i>
                                                            Editar
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item d-flex align-items-center text-danger deleteUser"
                                                            href="#" data-id="<?php echo $user['id']; ?>">
                                                            <i data-feather="trash-2" class="icon-sm me-2"></i>
                                                            Excluir
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
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
</div>

<!-- Modal de Visualização de Perfil -->
<div class="modal fade" id="viewProfileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Perfil do Usuário</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 border-end">
                        <div class="d-flex flex-column align-items-center text-center p-3">
                            <div class="avatar avatar-xl mb-3">
                                <div class="avatar-initial rounded-circle bg-primary">
                                    <span class="user-initials fs-2">JD</span>
                                </div>
                            </div>
                            <h4 class="mb-1" id="profileName">John Doe</h4>
                            <p class="text-muted mb-2" id="profileUsername">@johndoe</p>
                            <span class="badge bg-success mb-3">Ativo</span>
                            <div class="mt-3">
                                <button class="btn btn-primary me-2">
                                    <i data-feather="message-square" class="icon-sm me-2"></i>
                                    Mensagem
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="card-body">
                            <h6 class="mb-3">Informações do Usuário</h6>
                            <div class="row mb-3">
                                <div class="col-sm-4">
                                    <h6 class="mb-0"><i data-feather="user" class="icon-sm me-2"></i>Nome Completo</h6>
                                </div>
                                <div class="col-sm-8 text-muted" id="profileFullName">
                                    John Doe
                                </div>
                            </div>
                            <hr>
                            <div class="row mb-3">
                                <div class="col-sm-4">
                                    <h6 class="mb-0"><i data-feather="mail" class="icon-sm me-2"></i>Email</h6>
                                </div>
                                <div class="col-sm-8 text-muted" id="profileEmail">
                                    john.doe@example.com
                                </div>
                            </div>
                            <hr>
                            <div class="row mb-3">
                                <div class="col-sm-4">
                                    <h6 class="mb-0"><i data-feather="briefcase" class="icon-sm me-2"></i>Função</h6>
                                </div>
                                <div class="col-sm-8 text-muted" id="profileRole">
                                    Administrador
                                </div>
                            </div>
                            <hr>
                            <div class="row mb-3">
                                <div class="col-sm-4">
                                    <h6 class="mb-0"><i data-feather="calendar" class="icon-sm me-2"></i>Data de
                                        Registro</h6>
                                </div>
                                <div class="col-sm-8 text-muted" id="profileRegisterDate">
                                    01/01/2024
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Adicionar Usuário (Modernizado) -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i data-feather="user-plus" class="icon-sm me-2"></i>
                    Adicionar Usuário
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addUserForm">
                    <div class="mb-3">
                        <label for="username" class="form-label">
                            <i data-feather="at-sign" class="icon-sm me-2"></i>
                            Username
                        </label>
                        <input type="text" class="form-control" id="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">
                            <i data-feather="user" class="icon-sm me-2"></i>
                            Nome Completo
                        </label>
                        <input type="text" class="form-control" id="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i data-feather="mail" class="icon-sm me-2"></i>
                            Email
                        </label>
                        <input type="email" class="form-control" id="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i data-feather="lock" class="icon-sm me-2"></i>
                            Senha
                        </label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i data-feather="eye" class="icon-sm"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">
                            <i data-feather="shield" class="icon-sm me-2"></i>
                            Função
                        </label>
                        <select class="form-select" id="role" required>
                            <option value="admin">Administrador</option>
                            <option value="manager">Gerente</option>
                            <option value="waiter">Garçom</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i data-feather="x" class="icon-sm me-2"></i>
                    Cancelar
                </button>
                <button type="button" class="btn btn-primary" id="addUserButton">
                    <i data-feather="save" class="icon-sm me-2"></i>
                    Salvar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Editar Usuário (Modernizado) -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i data-feather="edit" class="icon-sm me-2"></i>
                    Editar Usuário
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editUserForm">
                    <input type="hidden" id="editUserId">
                    <div class="mb-3">
                        <label for="editUsername" class="form-label">
                            <i data-feather="at-sign" class="icon-sm me-2"></i>
                            Username
                        </label>
                        <input type="text" class="form-control" id="editUsername" required>
                    </div>
                    <div class="mb-3">
                        <label for="editName" class="form-label">
                            <i data-feather="user" class="icon-sm me-2"></i>
                            Nome Completo
                        </label>
                        <input type="text" class="form-control" id="editName" required>
                    </div>
                    <div class="mb-3">
                        <label for="editEmail" class="form-label">
                            <i data-feather="mail" class="icon-sm me-2"></i>
                            Email
                        </label>
                        <input type="email" class="form-control" id="editEmail" required>
                    </div>
                    <div class="mb-3">
                        <label for="editPassword" class="form-label for=" editPassword" class="form-label">
                            <i data-feather="lock" class="icon-sm me-2"></i>
                            Senha
                        </label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="editPassword"
                                placeholder="Deixe em branco para não alterar">
                            <button class="btn btn-outline-secondary" type="button" id="toggleEditPassword">
                                <i data-feather="eye" class="icon-sm"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editRole" class="form-label">
                            <i data-feather="shield" class="icon-sm me-2"></i>
                            Função
                        </label>
                        <select class="form-select" id="editRole" required>
                            <option value="admin">Administrador</option>
                            <option value="manager">Gerente</option>
                            <option value="waiter">Garçom</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i data-feather="x" class="icon-sm me-2"></i>
                    Cancelar
                </button>
                <button type="button" class="btn btn-primary" id="updateUserButton">
                    <i data-feather="save" class="icon-sm me-2"></i>
                    Atualizar
                </button>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Adicione este JavaScript no final da página -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializa os ícones Feather
    if (typeof feather !== 'undefined') {
        feather.replace();
    }

    // Inicializa o DataTable
    const userTable = new DataTable('#userTable', {
        order: [
            [0, 'desc']
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json'
        }
    });

    // Função para alternar visibilidade da senha
    function setupPasswordToggle(passwordId, toggleButtonId) {
        const passwordInput = document.getElementById(passwordId);
        const toggleButton = document.getElementById(toggleButtonId);

        if (toggleButton && passwordInput) {
            toggleButton.addEventListener('click', () => {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);

                const icon = toggleButton.querySelector('i');
                if (icon) {
                    icon.setAttribute('data-feather', type === 'password' ? 'eye' : 'eye-off');
                    feather.replace();
                }
            });
        }
    }

    // Configura os toggles de senha
    setupPasswordToggle('password', 'togglePassword');
    setupPasswordToggle('editPassword', 'toggleEditPassword');

    // Manipulador para visualização de perfil
    document.querySelectorAll('.viewProfile').forEach(button => {
        button.addEventListener('click', async (e) => {
            e.preventDefault();
            const userId = button.dataset.id;

            try {
                const response = await fetch(`/api/users/${userId}`);
                const userData = await response.json();

                // Atualiza os dados no modal de perfil
                document.getElementById('profileName').textContent = userData.name;
                document.getElementById('profileUsername').textContent =
                    `@${userData.username}`;
                document.getElementById('profileFullName').textContent = userData.name;
                document.getElementById('profileEmail').textContent = userData.email;
                document.getElementById('profileRole').textContent = userData.role;
                document.getElementById('profileRegisterDate').textContent =
                    new Date(userData.created_at).toLocaleDateString('pt-BR');

                // Atualiza as iniciais do avatar
                const initials = userData.name
                    .split(' ')
                    .map(n => n[0])
                    .join('')
                    .substr(0, 2)
                    .toUpperCase();
                document.querySelector('.user-initials').textContent = initials;

            } catch (error) {
                console.error('Erro ao carregar dados do usuário:', error);
                // Adicione aqui sua lógica de tratamento de erro
            }
        });
    });

    // Manipulador para edição de usuário
    document.querySelectorAll('.editUser').forEach(button => {
        button.addEventListener('click', async (e) => {
            e.preventDefault();
            const userId = button.dataset.id;

            try {
                const response = await fetch(`/api/users/${userId}`);
                const userData = await response.json();

                // Preenche o formulário de edição
                document.getElementById('editUserId').value = userData.id;
                document.getElementById('editUsername').value = userData.username;
                document.getElementById('editName').value = userData.name;
                document.getElementById('editEmail').value = userData.email;
                document.getElementById('editRole').value = userData.role;
                document.getElementById('editPassword').value = '';

            } catch (error) {
                console.error('Erro ao carregar dados do usuário:', error);
                // Adicione aqui sua lógica de tratamento de erro
            }
        });
    });

    // Manipulador para exclusão de usuário
    document.querySelectorAll('.deleteUser').forEach(button => {
        button.addEventListener('click', async (e) => {
            e.preventDefault();
            const userId = button.dataset.id;

            if (confirm('Tem certeza que deseja excluir este usuário?')) {
                try {
                    const response = await fetch(`/api/users/${userId}`, {
                        method: 'DELETE'
                    });

                    if (response.ok) {
                        // Recarrega a tabela ou remove a linha
                        button.closest('tr').remove();
                        // Adicione aqui sua lógica de feedback de sucesso
                    }
                } catch (error) {
                    console.error('Erro ao excluir usuário:', error);
                    // Adicione aqui sua lógica de tratamento de erro
                }
            }
        });
    });

    // Manipulador para adicionar usuário
    document.getElementById('addUserButton').addEventListener('click', async () => {
        const formData = {
            username: document.getElementById('username').value,
            name: document.getElementById('name').value,
            email: document.getElementById('email').value,
            password: document.getElementById('password').value,
            role: document.getElementById('role').value
        };

        try {
            const response = await fetch('/api/users', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            if (response.ok) {
                // Fecha o modal e recarrega a página
                const modal = bootstrap.Modal.getInstance(document.getElementById('addUserModal'));
                modal.hide();
                location.reload();
            }
        } catch (error) {
            console.error('Erro ao adicionar usuário:', error);
            // Adicione aqui sua lógica de tratamento de erro
        }
    });

    // Manipulador para atualizar usuário
    document.getElementById('updateUserButton').addEventListener('click', async () => {
        const userId = document.getElementById('editUserId').value;
        const formData = {
            username: document.getElementById('editUsername').value,
            name: document.getElementById('editName').value,
            email: document.getElementById('editEmail').value,
            password: document.getElementById('editPassword').value,
            role: document.getElementById('editRole').value
        };

        try {
            const response = await fetch(`/api/users/${userId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            if (response.ok) {
                // Fecha o modal e recarrega a página
                const modal = bootstrap.Modal.getInstance(document.getElementById('editUserModal'));
                modal.hide();
                location.reload();
            }
        } catch (error) {
            console.error('Erro ao atualizar usuário:', error);
            // Adicione aqui sua lógica de tratamento de erro
        }
    });
});

</script>