<?php
require_once '../config/config.php';
logout();
header('Location: ' . BASE_URL . '/index.php');
exit;