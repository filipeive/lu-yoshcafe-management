<!-- 404.php -->
 <?php 
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Página Não Encontrada</title>
    <style>
        body {
            text-align: center;
            background-color: #f0f0f0;
            font-family: Arial, sans-serif;
        }
        h1 {
            font-size: 5rem;
            margin-top: 20vh;
            color: #ff4040;
        }
        p {
            font-size: 1.5rem;
            color: #333;
        }
        a {
            color: #007bff;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <h1>404</h1>
    <p>Oops! A página que você está procurando não existe.</p>
    <a href="/">Voltar para a página inicial</a>
</body>
</html>
