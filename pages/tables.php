<?php
// No início do seu arquivo PHP, adicione:


if (isset($_GET['success'])) {
    echo '<div class="alert alert-success">' . htmlspecialchars($_GET['success']) . '</div>';
}

if (isset($_GET['error'])) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($_GET['error']) . '</div>';
}

require_once '../config/config.php';

// Função para lidar com erros e retornar respostas JSON
function handleError($message) {
    error_log($message);
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
    } else {
        header("Location: tables.php?error=" . urlencode($message));
        exit;
    }
}
if ($result['success']) {
    header("Location: tables.php?success=" . urlencode($result['message']));
    exit;
}


// Verifica se o método de requisição é POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'create':
            // Código para criar mesa
            break;

        case 'occupy':
            $table_id = $_POST['table_id'] ?? '';
            if (!$table_id) {
                handleError("ID da mesa é obrigatório.");
            }
            $result = update_table_status($table_id, 'occupied');
            echo json_encode(['success' => true, 'message' => "Table ID $table_id status updated to 'occupied'."]);
            exit;

        case 'free':
            $table_id = $_POST['table_id'] ?? '';
            if (!$table_id) {
                handleError("ID da mesa é obrigatório.");
            }
            $result = update_table_status($table_id, 'free');
            echo json_encode(['success' => true, 'message' => "Table ID $table_id status updated to 'free'."]);
            exit;

        // Adicione outros casos conforme necessário...

        default:
            handleError("Ação inválida.");
    }
}

// Verifica se o usuário está logado
require_login();

// A partir daqui, só renderize a página HTML para requisições GET
$tables = get_all_tables();

include '../includes/header.php';
?>


<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="my-4 card-title">Mesas</h4>
                <button type="button" class="btn btn-primary mb-3" data-toggle="modal" data-target="#createTableModal">
                    Adicionar Nova Mesa
                </button>
                <div class="row">
                    <?php foreach ($tables as $table): ?>
                    <div class="col-md-4 mb-4">
                        <div
                            class="card text-center <?php echo $table['real_status'] == 'livre' ? 'bg-light' : ($table['real_status'] == 'ocupada' ? 'bg-warning' : 'bg-danger'); ?>">
                            <div class="card-body">
                                <h5 class="card-title">Mesa <?php echo $table['number']; ?></h5>
                                <?php if ($table['group_id']): ?>
                                <span class="badge bg-info">Unida</span>
                                <button type="button" class="btn btn-danger btn-sm split-table"
                                    data-table-id="<?php echo $table['id']; ?>">
                                    Separar Mesa
                                </button>
                                <?php endif; ?>
                                <p class="card-text">Capacidade: <?php echo $table['capacity']; ?></p>
                                <p
                                    class="badge <?php echo $table['real_status'] == 'livre' ? 'bg-success' : ($table['real_status'] == 'ocupada' ? 'bg-warning' : 'bg-danger'); ?>">
                                    <?php echo ucfirst($table['real_status']); ?>
                                </p>

                                <div class="d-flex justify-content-around">
                                    <?php if ($table['real_status'] == 'livre'): ?>
                                    <button type="button" class="btn btn-primary btn-sm occupy-table"
                                        data-table-id="<?php echo $table['id']; ?>">
                                        Ocupar Mesa
                                    </button>
                                    <button type="button" class="btn btn-warning btn-sm merge-table"
                                        data-table-id="<?php echo $table['id']; ?>">
                                        Unir Mesas
                                    </button>
                                    <?php elseif ($table['real_status'] == 'ocupada'): ?>
                                    <?php if ($table['real_status'] == 'ocupada' || $table['group_id']): ?>
                                    <a href="create_order.php?table_id=<?php echo $table['id']; ?>"
                                        class="btn btn-success btn-sm">
                                        Criar Pedido
                                    </a>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-danger btn-sm free-table"
                                        data-table-id="<?php echo $table['id']; ?>">
                                        Liberar Mesa
                                    </button>
                                    <?php endif; ?>
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
<?php include "modais/modais_mesas.php" ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
$(document).ready(function() {
    /// Função para ocupar mesa
    $(document).on('click', '.occupy-table', function() {
        var tableId = $(this).data('table-id');
        $('#occupyTableId').val(tableId);
        $('#occupyTableModal').modal('show');
    });

    // Quando o formulário de ocupação da mesa é enviado
    $('#occupyTableForm').on('submit', function(e) {
        e.preventDefault();
        var tableId = $('#occupyTableId').val();

        $.post('table_management.php', {
            action: 'occupy',
            table_id: tableId
        }, function(response) {
            if (response.success) {
                // Atualize o status da mesa na interface
                $('#table-status-' + tableId).text('ocupada').removeClass('bg-light').addClass(
                    'bg-warning');
                showOccupyTableAlert();
            } else {
                Swal.fire({
                    title: 'Erro',
                    text: response.message,
                    icon: 'error'
                });
            }
        });

        $('#occupyTableModal').modal('hide');
    });

    // For free table modal
    $(document).on('click', '.free-table', function() {
        var tableId = $(this).data('table-id');
        $('#freeTableId').val(tableId);
        $('#freeTableModal').modal('show');
        $('#freeTableModal').on('hidden.bs.modal', function() {
            $('#freeTableId').val('');
        });
    });

    // SweetAlert para criação de mesa
    function showCreateTableAlert() {
        Swal.fire({
            title: 'Mesa criada com sucesso!',
            icon: 'success',
            confirmButtonText: 'OK'
        });
    }

    // SweetAlert para ocupação de mesa
    function showOccupyTableAlert() {
        Swal.fire({
            title: 'Mesa ocupada!',
            icon: 'success',
            confirmButtonText: 'OK'
        });
    }

    // SweetAlert para liberação de mesa
    function showFreeTableAlert() {
        Swal.fire({
            title: 'Mesa liberada!',
            icon: 'success',
            confirmButtonText: 'OK'
        });
    }
    // Funções para interceptar o envio de formulários
    $(document).ready(function() {
        $('#createTableForm').on('submit', function(e) {
            e.preventDefault();
            // Adiciona lógica para criar a mesa...
            showCreateTableAlert();
        });

        $('#occupyTableForm').on('submit', function(e) {
            e.preventDefault();
            // Adiciona lógica para ocupar a mesa...
            showOccupyTableAlert();
        });

        $('#freeTableForm').on('submit', function(e) {
            e.preventDefault();
            // Adiciona lógica para liberar a mesa...
            showFreeTableAlert();
        });
    });
    // Unir mesas
    $('.merge-table').click(function() {
        var tableId = $(this).data('table-id');
        var tableIds = prompt("Digite os IDs das mesas para unir (separados por vírgula):");
        if (tableIds) {
            $.post('table_management.php', {
                action: 'merge',
                table_ids: tableIds.split(',')
            }, function(response) {
                alert(response.message);
                if (response.success) {
                    location.reload();
                }
            });
        }
    });

    // Separar mesa
    $('.split-table').click(function() {
        var tableId = $(this).data('table-id');
        $.post('table_management.php', {
            action: 'split',
            table_id: tableId
        }, function(response) {
            alert(response.message);
            if (response.success) {
                location.reload();
            }
        });
    });
});
</script>
<?php include '../includes/footer.php'; ?>