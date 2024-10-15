<?php
require_once '../../config/config.php';
//require_once '../functions/menu_functions.php';
require_login();
require_admin();
$menuItems = get_active_menu_items();
$categories = get_menu_categories();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu para Impressão</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #2c3e50;
        }
        h2 {
            color: #34495e;
            border-bottom: 2px solid #34495e;
            padding-bottom: 5px;
        }
        .menu-item {
            margin-bottom: 15px;
        }
        .menu-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            float: left;
            margin-right: 15px;
        }
        .menu-item h3 {
            margin: 0;
            color: #2980b9;
        }
        .menu-item p {
            margin: 5px 0;
        }
        .price {
            font-weight: bold;
            color: #27ae60;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Nosso Menu</h1>
        
        <div class="no-print">
            <button onclick="window.print()">Imprimir Menu</button>
            <button onclick="window.close()">Fechar</button>
        </div>

        <?php foreach ($categories as $category): ?>
            <h2><?php echo htmlspecialchars($category); ?></h2>
            <?php 
            $categoryItems = array_filter($menuItems, function($item) use ($category) {
                return $item['category'] === $category;
            });
            foreach ($categoryItems as $item): 
            ?>
                <div class="menu-item">
                    <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                    <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                    <p><?php echo htmlspecialchars($item['description']); ?></p>
                    <p class="price">MZN <?php echo number_format($item['price'], 2, ',', '.'); ?></p>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
</body>
</html>