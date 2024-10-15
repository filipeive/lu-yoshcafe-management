<?php
require_once '../config/config.php';

require_login();
//require_admin();

//Obter lista de usuários
$users = get_all_users();

include '../includes/header.php';
?>

<div class="row">
    <div class="col-sm-12">
        <div class="home-tab">
            <div class="d-sm-flex align-items-center justify-content-between border-bottom">
                <h4 class="card-title">Usuários</h4>
            </div>
            <div class="row mt-4">
                <div class="col-12">
                    <div class="table-responsive">
                        <table class="table">
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
                                        <a href="edit_user.php?id=<?php echo $user['id']; ?>"
                                            class="btn btn-sm btn-primary">Editar</a>
                                        <a href="delete_user.php?id=<?php echo $user['id']; ?>"
                                            class="btn btn-sm btn-danger">Excluir</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Adicione mais funcionalidades conforme necessário -->
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>