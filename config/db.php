<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'filipe');
define('DB_PASS', 'senha');
define('DB_NAME', 'lu_yosh_catering');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}