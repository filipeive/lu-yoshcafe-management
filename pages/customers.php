<?php
require_once '../config/config.php';

require_login();

// Obter lista de clientes
$clients = get_all_clients();

include '../includes/header.php';
?>

<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="col-sm-12">
            <div class="home-tab">
                <div class="d-sm-flex align-items-center justify-content-between border-bottom">
                    <h4 class="card-title">Clientes</h4>
                </div>
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nome</th>
                                        <th>Email</th>
                                        <th>Telefone</th>
                                        <th>Endereço</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($clients as $client): ?>
                                    <tr>
                                        <td><?php echo $client['id']; ?></td>
                                        <td><?php echo $client['name']; ?></td>
                                        <td><?php echo $client['email']; ?></td>
                                        <td><?php echo $client['phone']; ?></td>
                                        <td><?php echo $client['address']; ?></td>
                                        <td>
                                            <a href="edit_client.php?id=<?php echo $client['id']; ?>"
                                                class="btn btn-sm btn-primary">Editar</a>
                                            <a href="delete_client.php?id=<?php echo $client['id']; ?>"
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
</div>

<?php include '../includes/footer.php'; ?>