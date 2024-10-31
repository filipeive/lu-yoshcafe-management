<?php

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

<style>
.table-card {
    transition: all 0.3s ease;
    border-radius: 10px;
    overflow: hidden; /* Para adicionar bordas arredondadas em todas as partes */
    border: 1px solid rgba(0, 0, 0, 0.1); /* Bordas sutis */
}

.table-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2); /* Sombra mais pronunciada */
}

.table-available {
    background: linear-gradient(135deg, #aeeeee 0%, #e0f7fa 100%); /* Cores mais frescas para mesas livres */
}

.table-occupied {
    background: linear-gradient(135deg, #ffe0b2 0%, #ffcc80 100%); /* Cores quentes para mesas ocupadas */
}

.table-with-order {
    background: linear-gradient(135deg, #ffe6cc 0%, #ffab91 100%); /* Cores suaves para mesas com pedidos */
}

.table-card .card-header {
    background-color: rgba(255, 255, 255, 0.9); /* Cabeçalho mais claro */
    border-bottom: 1px solid rgba(0, 0, 0, 0.2); /* Borda mais visível */
    color: #333; /* Texto mais escuro */
}

.table-meta .capacity,
.table-meta .status {
    font-weight: bold; /* Negrito para destaque */
    color: #444; /* Cor do texto */
}

.table-actions .btn-group,
.table-actions .btn-group-vertical {
    width: 100%;
}

.table-actions .btn {
    transition: background-color 0.3s ease, color 0.3s ease; /* Transição suave para os botões */
}

.table-actions .btn:hover {
    background-color: rgba(0, 0, 0, 0.1); /* Fundo sutil ao passar o mouse */
}

</style>


<div class="row">
    <div class="col-lg-12">
        <div class="card card-tables shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
                <h4 class="card-title mb-0">
                    <i class="mdi mdi-table-large mr-2"></i>Gestão de Mesas
                </h4>
                <!--<div class="header-actions">
                    <button type="button" class="btn btn-outline-light" data-toggle="modal"
                        data-target="#createTableModal">
                        <i class="mdi mdi-plus-circle-outline mr-1"></i>Adicionar Mesa
                    </button>
                </div>-->
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($tables as $table): ?>
                    <div class="col-xl-3 col-lg-4 col-md-8 mb-4">
                        <div class="table-card 
                                            <?php 
                                            $statusClass = match($table['real_status']) {
                                                'livre' => 'table-available',
                                                'ocupada' => 'table-occupied',
                                                'com_pedido' => 'table-with-order',
                                                default => 'table-default'
                                            };
                                            echo $statusClass;
                                            ?> 
                                            card overflow-hidden position-relative">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <span class="badge badge-pill badge-dark">
                                        Mesa <?php echo $table['number']; ?>
                                    </span>
                                </h5>
                                <?php if ($table['group_id']): ?>
                                <span class="badge badge-info">Unida</span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body text-center">
                                <div class="table-meta mb-3">
                                    <div class="capacity">
                                        <i class="mdi mdi-seat mr-2"></i>
                                        <?php echo $table['capacity']; ?> lugares
                                    </div>
                                    <div class="status mt-2">
                                        <span class="badge 
                                                            <?php 
                                                            $badgeClass = match($table['real_status']) {
                                                                'livre' => 'badge-success',
                                                                'ocupada' => 'badge-warning',
                                                                'com_pedido' => 'badge-info',
                                                                default => 'badge-secondary'
                                                            };
                                                            echo $badgeClass;
                                                            ?>">
                                            <?php echo ucfirst($table['real_status']); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="table-actions mt-3">
                                    <?php if ($table['real_status'] == 'livre'): ?>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-outline-primary btn-sm occupy-table"
                                            data-table-id="<?php echo $table['id']; ?>">
                                            <i class="mdi mdi-seat-recline-extra mr-1"></i>Ocupar
                                        </button>
                                        <button type="button" class="btn btn-outline-warning btn-sm merge-table"
                                            data-table-id="<?php echo $table['id']; ?>">
                                            <i class="mdi mdi-table-merge mr-1"></i>Unir
                                        </button>
                                    </div>
                                    <?php elseif ($table['real_status'] == 'com_pedido'): ?>
                                    <a href="view_order.php?order_id=<?php echo $table['id']; ?>"
                                        class="btn btn-outline-info btn-sm mb-2">
                                        <i class="mdi mdi-eye mr-1"></i>Ver Pedido
                                    </a>
                                    <?php elseif ($table['real_status'] == 'ocupada'): ?>
                                    <a href="create_order.php?table_id=<?php echo $table['id']; ?>"
                                        class="btn btn-outline-success btn-sm mb-2">
                                        <i class="mdi mdi-cart-plus mr-1"></i>Criar Pedido
                                    </a>
                                    <button type="button" class="btn btn-outline-danger btn-sm free-table"
                                        data-table-id="<?php echo $table['id']; ?>">
                                        <i class="mdi mdi-table-remove mr-1"></i>Liberar
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
    // Ocupar mesa - apenas abre modal e preenche ID
    $(document).on('click', '.occupy-table', function() {
        var tableId = $(this).data('table-id');
        $('#occupyTableId').val(tableId);
        $('#occupyTableModal').modal('show');
    });

    // Liberar mesa - apenas abre modal e preenche ID
    $(document).on('click', '.free-table', function() {
        var tableId = $(this).data('table-id');
        $('#freeTableId').val(tableId);
        $('#freeTableModal').modal('show');
    });

    // Unir mesas - abre modal
    $(document).on('click', '.merge-table', function() {
        $('#mergeTablesModal').modal('show');
    });

    // Separar mesa - abre modal e preenche ID
    $(document).on('click', '.split-table', function() {
        var tableId = $(this).data('table-id');
        $('#splitTableId').val(tableId);
        $('#splitTablesModal').modal('show');
    });

    // Processar ocupação da mesa via formulário modal
    $('#occupyTableModal form').on('submit', function(e) {
        e.preventDefault();
        var tableId = $('#occupyTableId').val();
        var $modal = $('#occupyTableModal');

        $.post('table_management.php', {
            action: 'occupy',
            table_id: tableId
        }, function(response) {
            $modal.modal('hide');
            if (response.success) {
                Swal.fire({
                    title: 'Mesa ocupada!',
                    text: response.message,
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        location.reload();
                    }
                });
            } else {
                Swal.fire('Erro', response.message, 'error');
            }
        }, 'json');
    });

    // Processar liberação da mesa via formulário modal
    $('#freeTableModal form').on('submit', function(e) {
        e.preventDefault();
        var tableId = $('#freeTableId').val();
        var $modal = $('#freeTableModal');

        $.post('table_management.php', {
            action: 'free',
            table_id: tableId
        }, function(response) {
            $modal.modal('hide');
            if (response.success) {
                Swal.fire({
                    title: 'Mesa liberada!',
                    text: response.message,
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        location.reload();
                    }
                });
            } else {
                Swal.fire('Erro', response.message, 'error');
            }
        }, 'json');
    });

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
        var $modal = $('#mergeTablesModal');

        if (selectedTables.length > 0) {
            $.post('table_management.php', {
                action: 'merge',
                table_ids: selectedTables
            }, function(response) {
                $modal.modal('hide');
                if (response.success) {
                    Swal.fire({
                        title: 'Mesas unidas!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            location.reload();
                        }
                    });
                } else {
                    Swal.fire('Erro', response.message, 'error');
                }
            }, 'json');
        } else {
            Swal.fire('Atenção', 'Por favor, selecione pelo menos uma mesa.', 'warning');
        }
    });

    // Separar mesa via formulário modal
    $('#splitTableForm').on('submit', function(e) {
        e.preventDefault();
        var tableId = $('#splitTableId').val();
        var $modal = $('#splitTablesModal');

        $.post('table_management.php', {
            action: 'split',
            table_id: tableId
        }, function(response) {
            $modal.modal('hide');
            if (response.success) {
                Swal.fire({
                    title: 'Mesa separada!',
                    text: response.message,
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        location.reload();
                    }
                });
            } else {
                Swal.fire('Erro', response.message, 'error');
            }
        }, 'json');
    });

    // Criar mesa
    $('#createTableForm').on('submit', function(e) {
        e.preventDefault();

        $.post('table_management.php', {
            action: 'create',
            // Adicione aqui outros dados do formulário necessários
        }, function(response) {
            if (response.success) {
                Swal.fire({
                    title: 'Mesa criada com sucesso!',
                    text: response.message,
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        location.reload();
                    }
                });
            } else {
                Swal.fire('Erro', response.message, 'error');
            }
        }, 'json');
    });
});
</script>

<?php include '../includes/footer.php'; ?>