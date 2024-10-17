<?php
require_once '../config/config.php';
require_login();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $number = isset($_POST['number']) ? intval($_POST['number']) : 0;
    $capacity = isset($_POST['capacity']) ? intval($_POST['capacity']) : 0;

    if ($number <= 0) {
        $error = "O número da mesa deve ser maior que zero.";
    } elseif ($capacity <= 0) {
        $error = "A capacidade da mesa deve ser maior que zero.";
    } else {
        // Verificar se já existe uma mesa com este número
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tables WHERE number = ?");
        $stmt->execute([$number]);
        if ($stmt->fetchColumn() > 0) {
            $error = "Já existe uma mesa com este número.";
        } else {
            // Inserir nova mesa
            $stmt = $pdo->prepare("INSERT INTO tables (number, capacity, status) VALUES (?, ?, 'free')");
            if ($stmt->execute([$number, $capacity])) {
                $success = "Mesa criada com sucesso.";
            } else {
                $error = "Erro ao criar a mesa.";
            }
        }
    }
}

include '../includes/header.php';
?>

<div class="row">
    <div class="col-lg-6 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Criar Nova Mesa</h4>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <form method="post">
                    <div class="form-group">
                        <label for="number">Número da Mesa</label>
                        <input type="number" class="form-control" id="number" name="number" required min="1">
                    </div>
                    <div class="form-group">
                        <label for="capacity">Capacidade</label>
                        <input type="number" class="form-control" id="capacity" name="capacity" required min="1">
                    </div>
                    <button type="submit" class="btn btn-primary">Criar Mesa</button>
                    <a href="tables.php" class="btn btn-secondary">Voltar</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>