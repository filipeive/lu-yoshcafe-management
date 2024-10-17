<?php
require_once '../config/config.php';
require_login();

if (!isset($_GET['id'])) {
    die('ID do pedido não fornecido.');
}

$order_id = $_GET['id'];

// Obter informações do pedido
$order = get_order_by_id($order_id);
if (!$order) {
    die('Pedido não encontrado.');
}

// Obter itens do pedido
$order_items = get_order_items($order_id);

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();
        
        // Atualizar cada item do pedido
        foreach ($_POST['items'] as $item_id => $data) {
            $quantity = (int) $data['quantity'];
            if ($quantity > 0) {
                // Atualizar quantidade
                $stmt = $pdo->prepare("UPDATE order_items SET quantity = ? WHERE id = ?");
                $stmt->execute([$quantity, $item_id]);
            } else {
                // Remover item se a quantidade for 0
                $stmt = $pdo->prepare("DELETE FROM order_items WHERE id = ?");
                $stmt->execute([$item_id]);
            }
        }
        
        // Atualizar o total do pedido (opcional, dependendo do seu sistema)
        $total_amount = calculate_order_total($order_id);
        $stmt = $pdo->prepare("UPDATE orders SET total_amount = ? WHERE id = ?");
        $stmt->execute([$total_amount, $order_id]);

        $pdo->commit();
        echo "<script>alert('Pedido atualizado com sucesso!'); window.location.href='dashboard.php';</script>";
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Erro ao atualizar pedido: " . $e->getMessage());
        echo "<script>alert('Erro ao atualizar o pedido.');</script>";
    }
}

include '../includes/header.php';
?>

<div class="container">
    <h2>Editar Pedido <?php echo $order['id']; ?></h2>

    <form method="POST">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Quantidade</th>
                        <th>Preço Unitário</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_items as $item): ?>
                    <tr>
                        <td><?php echo $item['product_name']; ?></td>
                        <td>
                            <input type="number" name="items[<?php echo $item['id']; ?>][quantity]" class="form-control" value="<?php echo $item['quantity']; ?>" min="0">
                        </td>
                        <td><?php echo number_format($item['unit_price'], 2); ?></td>
                        <td><?php echo number_format($item['unit_price'] * $item['quantity'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <button type="submit" class="btn btn-primary mt-3">Salvar Alterações</button>
        <a href="dashboard.php" class="btn btn-secondary mt-3">Cancelar</a>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.bundle.min.js"></script>

<?php include '../includes/footer.php'; ?>
