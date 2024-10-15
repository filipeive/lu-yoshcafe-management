<?php
require_once 'config/config.php';

// Verifique se já existe um administrador
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
$admin_count = $stmt->fetchColumn();

if ($admin_count > 0) {
    die("Um administrador já foi registrado. Esta página não está mais acessível.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $name = $_POST['name'];
    
    if (create_user($username, $password, $name, 'admin')) {
        $success = "Administrador registrado com sucesso. Você pode agora fazer login.";
    } else {
        $error = "Erro ao registrar o administrador.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Registrar Administrador - Lu & Yosh Catering</title>
    <link rel="stylesheet" href="public/assets/vendors/feather/feather.css">
    <link rel="stylesheet" href="public/assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="public/assets/vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" href="public/assets/vendors/typicons/typicons.css">
    <link rel="stylesheet" href="public/assets/vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="public/assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="public/assets/css/vertical-layout-light/style.css">
    <link rel="stylesheet" href="public/custom/custom.css">
</head>
<body>
  <div class="container-scroller">
    <div class="container-fluid page-body-wrapper full-page-wrapper">
      <div class="content-wrapper d-flex align-items-center auth px-0">
        <div class="row w-100 mx-0">
          <div class="col-lg-4 mx-auto">
            <div class="auth-form-light text-left py-5 px-4 px-sm-5">
              <div class="brand-logo">
                <img src="public/assets/images/logo.svg" alt="logo">
              </div>
              <h4>Registrar Administrador</h4>
              <form class="pt-3" method="POST">
                <div class="form-group">
                  <input type="text" class="form-control form-control-lg" name="username" placeholder="Usuário" required>
                </div>
                <div class="form-group">
                  <input type="password" class="form-control form-control-lg" name="password" placeholder="Senha" required>
                </div>
                <div class="form-group">
                  <input type="text" class="form-control form-control-lg" name="name" placeholder="Nome Completo" required>
                </div>
                <div class="mt-3">
                  <button type="submit" class="btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn">REGISTRAR</button>
                </div>
              </form>
              <?php if (isset($success)): ?>
                <div class="mt-3 text-success"><?php echo $success; ?></div>
              <?php endif; ?>
              <?php if (isset($error)): ?>
                <div class="mt-3 text-danger"><?php echo $error; ?></div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="public/assets/vendors/js/vendor.bundle.base.js"></script>
  <script src="public/assets/js/off-canvas.js"></script>
  <script src="public/assets/js/hoverable-collapse.js"></script>
  <script src="public/assets/js/template.js"></script>
  <script src="public/assets/js/settings.js"></script>
  <script src="public/assets/js/todolist.js"></script>
</body>
</html>