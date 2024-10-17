<?php
require_once '../config/config.php';
require_login();

$orders = order_get_all();

include '../includes/header.php';
?>

<div class="row">
  <div class="col-lg-12 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">Pedidos</h4>
        
        <?php if (isset($_SESSION['success_message'])): ?>
          <div class="alert alert-success" role="alert">
            <?php 
            echo $_SESSION['success_message']; 
            unset($_SESSION['success_message']);
            ?>
          </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
          <div class="alert alert-danger" role="alert">
            <?php 
            echo $_SESSION['error_message']; 
            unset($_SESSION['error_message']);
            ?>
          </div>
        <?php endif; ?>

        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>ID</th>
                <th>Mesa</th>
                <th>Total</th>
                <th>Status</th>
                <th>Data</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($orders as $order): ?>
              <tr>
                <td><?php echo $order['id']; ?></td>
                <td><?php echo $order['table_number']; ?></td>
                <td>MZN <?php echo number_format($order['total_amount'], 2, ',', '.'); ?></td>
                <td>
                  <span class="badge <?php echo $order['status'] == 'completed' ? 'bg-success' : 'bg-warning'; ?>">
                    <?php echo $order['status'] == 'completed' ? 'Concluído' : 'Ativo'; ?>
                  </span>
                </td>
                <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                <td>
                  <a href="view_order.php?id=<?php echo $order['id']; ?>" class="btn btn-info btn-sm">Ver</a>
                  <?php if ($order['status'] != 'completed'): ?>
                    <a href="edit_order.php?id=<?php echo $order['id']; ?>" class="btn btn-warning btn-sm">Editar</a>
                    <a href="gerir_pedidos/complete_order.php?id=<?php echo $order['id']; ?>" class="btn btn-success btn-sm" onclick="return confirm('Tem certeza que deseja finalizar este pedido e gerar uma venda?')">Finalizar</a>
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

<?php include '../includes/footer.php'; ?>