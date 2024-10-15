<?php
require_once '../config/config.php';
require_login();

$tables = get_all_tables();

include '../includes/header.php';
?>

<div class="row">
  <div class="col-lg-12 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">Mesas</h4>
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
                  <span class="badge <?php echo $table['status'] == 'occupied' ? 'bg-danger' : 'bg-success'; ?>">
                    <?php echo $table['status'] == 'occupied' ? 'Ocupada' : 'Livre'; ?>
                  </span>
                </td>
                <td>
                  <?php if ($table['status'] == 'free'): ?>
                    <a href="create_order.php?table_id=<?php echo $table['id']; ?>" class="btn btn-primary btn-sm">Iniciar Pedido</a>
                  <?php else: ?>
                    <a href="view_order.php?table_id=<?php echo $table['id']; ?>" class="btn btn-info btn-sm">Ver Pedido</a>
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