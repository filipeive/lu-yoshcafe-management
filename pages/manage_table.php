<?php
require_once '../../config/config.php';
require_login();

if (!isset($_GET['id'])) {
    die('ID da mesa não fornecido.');
}

$table_id = $_GET['id'];

// Obter informações da mesa
$table = get_table_by_id($table_id);
if (!$table) {
    die('Mesa não encontrada.');
}

// Verificar se há uma ação de atualização
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_POST['action'] == 'create') {
        $number = $_POST['number'];
        $capacity = $_POST['capacity'];
        $status = 'free'; // Definindo o status padrão como 'livre'
        table_create($number, $capacity, $status);
        header("Location: tables.php"); // Redireciona para a página após a criação
        exit;
    } elseif ($_POST['action'] == 'occupy') {
        $table_id = $_POST['table_id'];
        update_table_status($table_id, 'occupied');
    } elseif ($_POST['action'] == 'free') {
        $table_id = $_POST['table_id'];
        update_table_status($table_id, 'free');
    }
}

include '../includes/header.php';
?>

<div class="container">
    <h2>Gerenciar Mesa <?php echo $table['number']; ?></h2>
    <!-- Botão para abrir o modal -->
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#manageTableModal">
        Gerenciar Mesa
    </button>
</div>

<!-- Modal -->
<div class="modal fade" id="manageTableModal" tabindex="-1" aria-labelledby="manageTableModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="manageTableModalLabel">Gerenciar Mesa <?php echo $table['number']; ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form method="POST">
          <div class="form-group">
            <label>Status Atual:</label>
            <span class="badge <?php echo ($table['status'] === 'ocupada') ? 'bg-danger' : 'bg-success'; ?>">
                <?php echo $table['status']; ?>
            </span>
          </div>
          <div class="form-group">
            <label>Novo Status:</label>
            <select name="status" class="form-control">
              <option value="livre" <?php if ($table['status'] === 'free') echo 'selected'; ?>>Livre</option>
              <option value="ocupada" <?php if ($table['status'] === 'occupied') echo 'selected'; ?>>Ocupada</option>
            </select>
          </div>
          
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary" name="update_status">Atualizar Status</button>
          </div>
        </form>
        <hr>
        <form method="POST">
          <!-- Ação para criar pedido -->
          <div class="form-group">
            <label>Criar Pedido</label>
            <button type="submit" class="btn btn-success mt-2" name="create_order">Adicionar Pedido</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.bundle.min.js"></script>

<?php include '../includes/footer.php'; ?>
