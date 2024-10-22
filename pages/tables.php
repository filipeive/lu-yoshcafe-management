<?php
// No início do seu arquivo PHP, adicione:

if (isset($_GET['success'])) {
    echo '<div class="alert alert-success">' . htmlspecialchars($_GET['success']) . '</div>';
}

if (isset($_GET['error'])) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($_GET['error']) . '</div>';
}

require_once '../config/config.php';

// Função para lidar com erros e retornar respostas JSON ou redirecionar
function handleError($message) {
    error_log($message);
    if (is_ajax_request()) {
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
    } else {
        header("Location: tables.php?error=" . urlencode($message));
        exit;
    }
}

// Função para verificar se a requisição é AJAX
function is_ajax_request() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
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
    // Ocupar mesa - abre modal e preenche ID
    $(document).on('click', '.occupy-table', function() {
        var tableId = $(this).data('table-id');
        $('#occupyTableId').val(tableId); // Preencher o ID da mesa no modal
        $('#occupyTableModal').modal('show'); // Exibir o modal
    });

    // Liberar mesa - abre modal e preenche ID
    $(document).on('click', '.free-table', function() {
        var tableId = $(this).data('table-id');
        $('#freeTableId').val(tableId); // Preencher o ID da mesa no modal
        $('#freeTableModal').modal('show'); // Exibir o modal
    });

    // Unir mesas - abre modal
    $(document).on('click', '.merge-table', function() {
        $('#mergeTablesModal').modal('show'); // Exibir o modal
    });

    // Separar mesa - abre modal e preenche ID
    $(document).on('click', '.split-table', function() {
        var tableId = $(this).data('table-id');
        $('#splitTableId').val(tableId); // Preencher o ID da mesa no modal
        $('#splitTablesModal').modal('show'); // Exibir o modal
    });

    // Processar ocupação da mesa via formulário modal
    $('#occupyTableModal form').on('submit', function(e) {
        e.preventDefault();
        var tableId = $('#occupyTableId').val();

        $.post('table_management.php', {
            action: 'occupy',
            table_id: tableId
        }, function(response) {
            if (response.success) {
                Swal.fire('Sucesso', response.message, 'success');
                location.reload();
            } else {
                Swal.fire('Erro', response.message, 'error');
            }
        }, 'json');
    });

    // Processar liberação da mesa via formulário modal
    $('#freeTableModal form').on('submit', function(e) {
        e.preventDefault();
        var tableId = $('#freeTableId').val();

        $.post('table_management.php', {
            action: 'free',
            table_id: tableId
        }, function(response) {
            if (response.success) {
                Swal.fire('Sucesso', response.message, 'success');
                location.reload();
            } else {
                Swal.fire('Erro', response.message, 'error');
            }
        }, 'json');
    });

    // Unir mesas via formulário modal
    $(document).ready(function() {
    // Selecionar/Deselecionar mesa
    $(document).on('click', '.table-card', function() {
        $(this).toggleClass('selected');
    });
    
    // Manipulação do envio do formulário de união
    $('#mergeTablesForm').on('submit', function(e) {
        e.preventDefault();
        var selectedTables = $('.table-card.selected').map(function() {
            return $(this).data('table-id');
        }).get();

        if (selectedTables.length > 0) {
            $.post('table_management.php', {
                action: 'merge',
                table_ids: selectedTables
            }, function(response) {
                if (response.success) {
                    Swal.fire('Sucesso', response.message, 'success');
                    location.reload();
                } else {
                    Swal.fire('Erro', response.message, 'error');
                }
            }, 'json');
        } else {
            Swal.fire('Atenção', 'Por favor, selecione pelo menos uma mesa.', 'warning');
        }
    });
});


    // Separar mesa via formulário modal
    $('#splitTableForm').on('submit', function(e) {
        e.preventDefault();
        var tableId = $('#splitTableId').val();

        $.post('table_management.php', {
            action: 'split',
            table_id: tableId
        }, function(response) {
            if (response.success) {
                Swal.fire('Sucesso', response.message, 'success');
                location.reload();
            } else {
                Swal.fire('Erro', response.message, 'error');
            }
        }, 'json');
    });
});


// Funções para interceptar o envio de formulários
$('#createTableForm').on('submit', function(e) {
    e.preventDefault();
    // Adiciona lógica para criar a mesa...
    Swal.fire({
        title: 'Mesa criada com sucesso!',
        icon: 'success',
        confirmButtonText: 'OK'
    });
});

$('#occupyTableForm').on('submit', function(e) {
    e.preventDefault();
    // Adiciona lógica para ocupar a mesa...
    Swal.fire({
        title: 'Mesa ocupada!',
        icon: 'success',
        confirmButtonText: 'OK'
    });
});

$('#freeTableForm').on('submit', function(e) {
    e.preventDefault();
    // Adiciona lógica para liberar a mesa...
    Swal.fire({
        title: 'Mesa liberada!',
        icon: 'success',
        confirmButtonText: 'OK'
    });
});
</script>

<?php include '../includes/footer.php'; ?>