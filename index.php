<?php
session_start();
error_log("Session started. usuario_id: " . (isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : 'not set'));
error_log("Session data: " . print_r($_SESSION, true));

require_once 'config/config.php'; // Arquivo de configuração contendo funções

// Verificar se o usuário já está logado
if (isset($_SESSION['user_id'])) {
    error_log("User already logged in. Redirecting to dashboard");
    redirect("pages/dashboard.php");
    exit();
}

$erro = ''; // Inicializar variável de erro

// Verificar se os dados de login foram enviados
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (login($username, $password)) {
        // Redireciona o usuário logado
        redirect("pages/dashboard.php");
        exit();
    } else {
        $erro = "Nome de usuário ou senha incorretos";
    }
}

// Função para redirecionamento seguro
function redirect($url) {
    if (!headers_sent()) {
        header("Location: $url");
        exit();
    } else {
        echo "<script>window.location.href='$url';</script>";
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Lu & Yosh Catering</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: url('public/assets/images/restaurant-bg.jpg') no-repeat center center fixed;
            background-size: cover;
            height: 100vh;
        }
        .login-container {
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 40px;
            max-width: 400px;
            width: 90%;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 30px;
        }
        .form-control {
            border-radius: 25px;
            padding: 12px 20px;
        }
        .btn-login {
            border-radius: 25px;
            padding: 12px 20px;
            font-weight: 600;
            background-color: #ff6b6b;
            border: none;
            transition: all 0.3s;
        }
        .btn-login:hover {
            background-color: #ff5252;
            transform: translateY(-2px);
        }
        .error-message {
            color: #dc3545;
            font-size: 14px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="login-container text-center">
            <img src="public/assets/images/Logo.png" alt="Lu & Yosh Catering" class="logo">
            <h2 class="mb-4">Lu & Yosh Catering</h2>

            <?php if ($erro): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $erro; ?>
            </div>
            <?php endif; ?>

            <form method="POST" class="text-start">
                <div class="mb-3">
                    <input type="username" class="form-control" name="username" placeholder="Usuario">
                </div>
                <div class="mb-4">
                    <input type="password" class="form-control" name="password" placeholder="Senha" required>
                </div>
                <button type="submit" class="btn btn-primary btn-login w-100">Entrar</button>
            </form>

            <p class="mt-4 mb-0">Esqueceu sua password? <a href="#">Clique aqui</a></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
