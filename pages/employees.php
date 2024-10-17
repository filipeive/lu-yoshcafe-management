<?php
require_once '../config/config.php';

require_login();
require_admin();

// Obter lista de funcionários
$employees = get_all_employees();

include '../includes/header.php';
?>


<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="card-description">
                    <h4 class="card-title">Funcionarios</h4>
                </div>
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nome</th>
                                        <th>Função</th>
                                        <th>Data de Contratação</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($employees as $employee): ?>
                                    <tr>
                                        <td><?php echo $employee['id']; ?></td>
                                        <td><?php echo $employee['name']; ?></td>
                                        <td><?php echo $employee['role']; ?></td>
                                        <td><?php echo $employee['hire_date']; ?></td>
                                        <td>
                                            <a href="edit_employee.php?id=<?php echo $employee['id']; ?>"
                                                class="btn btn-sm btn-primary">Editar</a>
                                            <a href="delete_employee.php?id=<?php echo $employee['id']; ?>"
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