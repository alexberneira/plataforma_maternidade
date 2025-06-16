<?php
require_once 'init.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erro - Plataforma de Maternidade</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding-top: 2rem;
        }
        .error-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 2rem;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .error-title {
            color: #dc3545;
            margin-bottom: 1rem;
        }
        .error-message {
            color: #6c757d;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-container">
            <h1 class="error-title">Ocorreu um erro</h1>
            <p class="error-message">
                <?php
                if (isset($_SESSION['db_error'])) {
                    echo htmlspecialchars($_SESSION['db_error']);
                    unset($_SESSION['db_error']);
                } else {
                    echo "Ocorreu um erro inesperado. Por favor, tente novamente mais tarde.";
                }
                ?>
            </p>
            <a href="index.php" class="btn btn-primary">Voltar para a página inicial</a>
        </div>
    </div>
</body>
</html> 