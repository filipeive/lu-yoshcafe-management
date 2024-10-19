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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['table_ids'])) {
    $table_ids = $_POST['table_ids'];
    merge_tables($table_ids); // Chama a função para unir mesas
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
                                
                                <?php endif; ?>
                                <p class="card-text">Capacidade: <?php echo $table['capacity']; ?></p>
                                <p
                                    class="badge <?php echo $table['real_status'] == 'livre' ? 'bg-success' : ($table['real_status'] == 'ocupada' ? 'bg-warning' : 'bg-danger'); ?>">
                                    <?php echo ucfirst($table['real_status']); ?>
                                </p>

                                <div class="d-flex justify-content-around">
                                    <?php if ($table['real_status'] == 'livre'): ?>
                                    <!-- Botão para ocupar mesa -->
                                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal"
                                        data-target="#occupyTableModal" data-table-id="<?php echo $table['id']; ?>">
                                        Ocupar Mesa
                                    </button>

                                    <!-- Botão para unir mesas, aparece apenas se a mesa estiver livre -->
                                    <button type="button" class="btn btn-warning btn-sm" data-toggle="modal"
                                        data-target="#mergeTablesModal" data-table-id="<?php echo $table['id']; ?>">
                                        Unir Mesas
                                    </button>
                                    <?php elseif ($table['real_status'] == 'ocupada'): ?>
                                    <!-- Botão para criar pedido -->
                                    <?php if ($table['real_status'] == 'ocupada' || $table['group_id']): ?>
                                    <a href="create_order.php?table_id=<?php echo $table['id']; ?>"
                                        class="btn btn-success btn-sm">
                                        Criar Pedido
                                    </a>
                                    <?php endif; ?>
                                    <!-- Botão para liberar mesa -->
                                    <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                                        data-target="#freeTableModal" data-table-id="<?php echo $table['id']; ?>">
                                        Liberar Mesa
                                    </button>

                                    <?php if ($table['group_id']): ?>
                                    
                                    <?php endif; ?>
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

    <?php include "modais/modais_mesas.php" ?>
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
    // Table merging functionality
$('#mergeTablesForm').on('submit', function(e) {
    e.preventDefault();
    const tableIds = $('input[name="table_ids[]"]:checked').map(function() {
        return $(this).val();
    }).get();

    if (tableIds.length < 2) {
        Swal.fire({
            title: 'Erro',
            text: 'Selecione pelo menos duas mesas para unir.',
            icon: 'error'
        });
        return;
    }

    $.ajax({
        url: 'table_management.php',
        method: 'POST',
        data: {
            action: 'merge',
            table_ids: tableIds
        },
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    title: 'Sucesso',
                    text: 'Mesas unidas com sucesso!',
                    icon: 'success'
                }).then(() => location.reload());
            } else {
                Swal.fire({
                    title: 'Erro',
                    text: response.message || 'Erro ao unir mesas',
                    icon: 'error'
                });
            }
        },
        error: function() {
            Swal.fire({
                title: 'Erro',
                text: 'Erro na comunicação com o servidor',
                icon: 'error'
            });
        }
    });
});

// Table splitting functionality
$(document).on('click', '.split-table-btn', function(e) {
    e.preventDefault();
    const tableId = $(this).data('table-id');

    Swal.fire({
        title: 'Confirmar separação',
        text: 'Deseja separar esta mesa do grupo?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sim',
        cancelButtonText: 'Não'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'table_management.php',
                method: 'POST',
                data: {
                    action: 'split',
                    table_id: tableId
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Sucesso',
                            text: 'Mesa separada com sucesso!',
                            icon: 'success'
                        }).then(() => location.reload());
                    } else {
                        Swal.fire({
                            title: 'Erro',
                            text: response.message || 'Erro ao separar mesa',
                            icon: 'error'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        title: 'Erro',
                        text: 'Erro na comunicação com o servidor',
                        icon: 'error'
                    });
                }
            });
        }
    });
});
    </script>
    <?php include '../includes/footer.php'; ?>