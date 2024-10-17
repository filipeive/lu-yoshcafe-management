<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../config/config.php';

// Verifica se o método de requisição é POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_POST['action'] == 'create') {
        $number = $_POST['number'];
        $capacity = $_POST['capacity'];
        $status = 'free'; // O status inicial será "livre"
        
        // Chama a função para criar uma nova mesa no banco de dados
        create_table($number, $capacity, $status);
    } elseif ($_POST['action'] == 'occupy') {
        $table_id = $_POST['table_id'];
        update_table_status($table_id, 'occupied');
    } elseif ($_POST['action'] == 'free') {
        $table_id = $_POST['table_id'];
        update_table_status($table_id, 'free');
    }
}

// Função para criar uma nova mesa no banco de dados
function create_table($number, $capacity, $status) {
    global $pdo;

    $stmt = $pdo->prepare("INSERT INTO tables (number, capacity, status) VALUES (?, ?, ?)");
    $stmt->execute([$number, $capacity, $status]);

    if ($stmt) {
        error_log("Nova mesa criada com sucesso: Número $number, Capacidade $capacity");
    } else {
        error_log("Erro ao criar nova mesa.");
    }
}

// Verifica se o usuário está logado
require_login();

$tables = get_all_tables();

include '../includes/header.php';
?>

<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Mesas</h4>
                <button type="button" class="btn btn-primary mb-3" data-toggle="modal" data-target="#createTableModal">
                    Adicionar Nova Mesa
                </button>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Número</th>
                                <th>Capacidade</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tables as $table): ?>
                            <tr>
                                <td><?php echo $table['number']; ?></td>
                                <td><?php echo $table['capacity']; ?></td>
                                <td>
                                    <?php
                                switch ($table['real_status']) {
                                    case 'livre':
                                        echo '<span class="badge bg-success">Livre</span>';
                                        break;
                                    case 'ocupada':
                                        echo '<span class="badge bg-warning">Ocupada</span>';
                                        break;
                                    case 'com_pedido':
                                        echo '<span class="badge bg-danger">Com Pedido</span>';
                                        break;
                                }
                                ?>
                                </td>
                                <td>
                                    <?php if ($table['real_status'] == 'livre'): ?>
                                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal"
                                        data-target="#occupyTableModal" data-table-id="<?php echo $table['id']; ?>">
                                        Ocupar Mesa
                                    </button>
                                    <?php elseif ($table['real_status'] == 'ocupada'): ?>
                                    <a href="create_order.php?table_id=<?php echo $table['id']; ?>"
                                        class="btn btn-success btn-sm">Criar Pedido</a>
                                    <button type="button" class="btn btn-secondary btn-sm" data-toggle="modal"
                                        data-target="#freeTableModal" data-table-id="<?php echo $table['id']; ?>">
                                        Liberar Mesa
                                    </button>
                                    <?php else: ?>
                                    <a href="edit_order.php?table_id=<?php echo $table['id']; ?>"
                                        class="btn btn-info btn-sm">Ver Pedido</a>
                                    <?php endif; ?>
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

<!-- Modal para criar mesa -->
<div class="modal fade" id="createTableModal" tabindex="-1" role="dialog" aria-labelledby="createTableModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createTableModalLabel">Criar Nova Mesa</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    <div class="form-group">
                        <label for="number">Número da Mesa</label>
                        <input type="number" class="form-control" id="number" name="number" required min="1">
                    </div>
                    <div class="form-group">
                        <label for="capacity">Capacidade</label>
                        <input type="number" class="form-control" id="capacity" name="capacity" required min="1">
                    </div>
                    <!-- O status será inicializado como 'livre' -->
                    <input type="hidden" name="status" value="free">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-primary">Criar Mesa</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para ocupar mesa -->
<div class="modal fade" id="occupyTableModal" tabindex="-1" role="dialog" aria-labelledby="occupyTableModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="occupyTableModalLabel">Ocupar Mesa</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="occupy">
                    <input type="hidden" name="table_id" id="occupyTableId">
                    <p>Tem certeza que deseja ocupar esta mesa?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Ocupar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para liberar mesa -->
<div class="modal fade" id="freeTableModal" tabindex="-1" role="dialog" aria-labelledby="freeTableModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="freeTableModalLabel">Liberar Mesa</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="free">
                    <input type="hidden" name="table_id" id="freeTableId">
                    <p>Tem certeza que deseja liberar esta mesa?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Liberar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
$('#occupyTableModal').on('show.bs.modal', function(event) {
    var button = $(event.relatedTarget);
    var tableId = button.data('table-id');
    var modal = $(this);
    modal.find('#occupyTableId').val(tableId);
});

$('#freeTableModal').on('show.bs.modal', function(event) {
    var button = $(event.relatedTarget);
    var tableId = button.data('table-id');
    var modal = $(this);
    modal.find('#freeTableId').val(tableId);
});
</script>

<?php include '../includes/footer.php'; ?>
