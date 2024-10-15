<?php
require_once '../config/config.php';
require_once '../includes/functions.php';

require_login();

$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: orders.php');
    exit;
}

$stmt = $pdo->prepare("SELECT o.*, t.number as table_number FROM orders o LEFT JOIN tables t ON o.table_id = t.id WHERE o.id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: orders.php');
    exit;
}

$stmt = $pdo->prepare("SELECT oi.*, p.name as product_name, p.price FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$stmt->execute([$id]);
$order_items = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="row">
  <div class="col-lg-12 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">Detalhes do Pedido #<?php echo $order['id']; ?></h4>
        <p class="card-description">
          Mesa: <?php echo $order['table_number'] ? $order['table_number'] : 'N/A'; ?> |
          Status: <?php echo $order['status'] == 'completed' ? 'Concluído' : 'Ativo'; ?> |
          Data: <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
        </p>
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Produto</th>
                <th>Quantidade</th>
                <th>Preço Unitário</th>
                <th>Subtotal</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($order_items as $item): ?>
              <tr>
                <td><?php echo $item['product_name']; ?></td>
                <td><?php echo $item['quantity']; ?></td>
                <td>R$ <?php echo number_format($item['price'], 2, ',', '.'); ?></td>
                <td>R$ <?php echo number_format($item['quantity'] * $item['price'], 2, ',', '.'); ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot>
              <tr>
                <th colspan="3" class="text-right">Total:</th>
                <th>R$ <?php echo number_format($order['total_amount'], 2, ',', '.'); ?></th>
              </tr>
            </tfoot>
          </table>
        </div>
        <?php if ($order['status'] != 'completed'): ?>
          <div class="mt-3">
            <a href="edit_order.php?id=<?php echo $order['id']; ?>" class="btn btn-warning">Editar Pedido</a>
            <a href="complete_order.php?id=<?php echo $order['id']; ?>" class="btn btn-success" onclick="return confirm('Tem certeza que deseja concluir este pedido?')">Concluir Pedido</a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>