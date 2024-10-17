<?php
require_once '../config/config.php';

// Inicia a sessão se ainda não foi iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

try {
    // Conecta ao banco de dados usando PDO
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Busca as informações do usuário
    $stmt = $pdo->prepare("SELECT username, name, role FROM users WHERE id = :id");
    $stmt->bindParam(':id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Se não encontrou o usuário, redireciona para o login
    if (!$usuario) {
        session_destroy();
        header("Location: ../index.php");
        exit;
    }
} catch (PDOException $e) {
    // Log do erro e redirecionamento para uma página de erro
    error_log("Erro de banco de dados: " . $e->getMessage());
    header("Location: .error.php");
    exit;
}

// O restante do seu código HTML permanece o mesmo
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Lu & Yosh Catering</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="../public/assets/vendors/feather/feather.css">
    <link rel="stylesheet" href="../public/assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="../public/assets/vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" href="../public/assets/vendors/typicons/typicons.css">
    <link rel="stylesheet" href="../public/assets/vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="../public/assets/vendors/css/vendor.bundle.base.css">
    <!-- endinject -->
    <link rel="stylesheet" href="../public/assets/css/vertical-layout-light/style.css">
    <link rel="shortcut icon" href="../public/assets/images/favicon.png" />
    <!-- Custom CSS -->
    <!--<link rel="stylesheet" href="../public/css/sb-admin-2.css">-->
    <!--<link rel="stylesheet" href="../public/css/sb-admin-2.min.css">-->
    <style>
    /* Estilo geral do dashboard */
    /* Estilo geral do dashboard */
    body,
    .main-panel,
    .content-wrapper {
        background-color: #1C1C1C;
        /* Fundo fallback para caso a imagem não carregue */
        color: #F5F5F5;
    }

    /* Estilizando o background com o pseudo-elemento */
    body::before,
    .main-panel::before,
    .content-wrapper::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: url('../public/assets/images/restaurant-bg.jpg');
        /* Adicione o caminho da imagem aqui */
        background-size: cover;
        background-repeat: no-repeat;
        background-position: center;
        opacity: 0.5;
        /* Transparência para que o conteúdo da frente seja visível */
        z-index: -1;
        /* Coloca a imagem atrás do conteúdo */
    }

    /* Ajustes adicionais para layout */
    body,
    .main-panel,
    .content-wrapper {
        z-index: 1;
        /* Coloca o conteúdo em cima do fundo */
        position: relative;
        background: rgba(255, 255, 255, 0.5);
        /* Fundo semi-transparente */
    }

    /* Cartões */
    .card {
        border-radius: 0.5rem;
        background: rgba(255, 255, 255, 0.9);
        /* Fundo branco com transparência */
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    /* Estilo da tabela */
    .table {
        background: rgba(255, 255, 255, 0.8);
    }

    .table-hover tbody tr:hover {
        background-color: rgba(200, 200, 200, 0.3);
        /* Cor ao passar o mouse */
    }

    /* Cartões coloridos 
    .card-stat {
        border-radius: 0.5rem;
        padding: 2rem;
        margin: 1rem;
    }*/

    /* Estilos para estatísticas */
    .bg-primary {
        background-color: #007bff !important;
        color: #fff;
    }

    .bg-success {
        background-color: #28a745 !important;
    }

    .bg-danger {
        background-color: #dc3545 !important;
    }
    </style>
</head>

<body>
    <div class="container-scroller">
        <!-- partial:partials/_navbar.html -->
        <nav class="navbar default-layout col-lg-12 col-12 p-0 fixed-top d-flex align-items-top flex-row"
            style="border-bottom:1px orange solid">
            <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-start">
                <div class="me-3">
                    <button class="navbar-toggler navbar-toggler align-self-center" type="button"
                        data-bs-toggle="minimize">
                        <span class="icon-menu"></span>
                    </button>
                </div>
                <div>
                    <a class="navbar-brand brand-logo" href="dashboard.php">
                        <img src="../public/assets/images/Logo.png" alt="logo" />
                    </a>
                    <a class="navbar-brand brand-logo-mini" href="index.php">
                        <img src="../public/assets/images/logo-mini.svg" alt="logo" />
                    </a>
                </div>
            </div>
            <div class="navbar-menu-wrapper d-flex align-items-top">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown d-none d-lg-block user-dropdown">
                        <a class="nav-link" id="UserDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                            <img class="img-xs rounded-circle" src="../public/assets/images/faces/face8.jpg"
                                alt="Profile image">
                        </a>
                        <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="UserDropdown">
                            <div class="dropdown-header text-center">
                                <img class="img-md rounded-circle" src="../public/assets/images/faces/face.jpg"
                                    alt="Profile image">
                                <p class="mb-1 mt-3 font-weight-semibold"><?php echo $usuario['name'];  ?></p>
                                <p class="fw-light text-muted mb-0"><?php echo $usuario['username']; ?></p>
                            </div>
                            <a class="dropdown-item"><i
                                    class="dropdown-item-icon mdi mdi-account-outline text-primary me-2"></i> Meu
                                Perfil</a>
                            <a class="dropdown-item" href="../actions/logout.php"><i
                                    class="dropdown-item-icon mdi mdi-power text-primary me-2"></i>Sair</a>
                        </div>
                    </li>
                </ul>
                <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button"
                    data-bs-toggle="offcanvas">
                    <span class="mdi mdi-menu"></span>
                </button>
            </div>
        </nav>
        <!-- partial -->
        <div class="container-fluid page-body-wrapper">
            <!-- partial:partials/_sidebar.html -->
            <nav class="sidebar sidebar-offcanvas" id="sidebar" style="border-left: orange 1px solid !important;">
                <br>
                <ul class="nav">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="mdi mdi-view-dashboard menu-icon"></i>
                            <span class="menu-title">Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="sales.php">
                            <i class="mdi mdi-cash-register menu-icon"></i>
                            <span class="menu-title">Vendas</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="orders.php">
                            <i class="mdi mdi-food-fork-drink menu-icon"></i>
                            <span class="menu-title">Pedidos</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">
                            <i class="mdi mdi-silverware-fork-knife menu-icon"></i>
                            <span class="menu-title">Gerir Estoque de Produtos</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="tables.php">
                            <i class="mdi mdi-table-chair menu-icon"></i>
                            <span class="menu-title">Mesas</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="menus.php">
                            <i class="mdi mdi-food-variant menu-icon"></i>
                            <span class="menu-title">Gerenciar Menu</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="customers.php">
                            <i class="mdi mdi-account-multiple menu-icon"></i>
                            <span class="menu-title">Clientes</span>
                        </a>
                    </li>
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="employees.php">
                            <i class="mdi mdi-account-group menu-icon"></i>
                            <span class="menu-title">Funcionários</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php">
                            <i class="mdi mdi-chart-bar menu-icon"></i>
                            <span class="menu-title">Relatórios</span>
                        </a>
                    </li>
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">
                            <i class="mdi mdi-account-cog menu-icon"></i>
                            <span class="menu-title">Usuários</span>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <!-- partial -->
            <div class="main-panel">
                <div class="content-wrapper">