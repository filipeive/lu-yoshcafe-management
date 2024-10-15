<?php
// Configurações gerais
define('SITE_NAME', 'Lu & Yosh Catering');
define('BASE_URL', 'http://localhost/LuYoshCatering'); // Ajuste conforme necessário
// Configurações de sessão
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
// Inclui o arquivo de conexão com o banco de dados
require_once 'db.php';
// Inclui o arquivo de funções
require_once 'user_functions.php';
require_once 'auth_functions.php';
require_once 'functions_orders.php';
require_once 'functions_products.php';
require_once 'functions_tables.php';
require_once 'employees_functions.php';
require_once 'reports_functions.php';
require_once 'functions_menu.php';
require_once 'functions_clients.php';
require_once 'functions_sales.php';