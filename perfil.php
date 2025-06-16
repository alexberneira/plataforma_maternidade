<?php
require_once 'init.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['user_email'])) {
    header('Location: login.php');
    exit;
}

// Busca informações do usuário
try {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$_SESSION['user_email']]);
    $usuario = $stmt->fetch();
} catch(PDOException $e) {
    $_SESSION['error'] = "Erro ao buscar informações do usuário";
    header('Location: error.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil - Plataforma de Maternidade</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .profile-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .profile-header {
            text-align: center;
            margin-bottom: 2rem;
            color: #e83e8c;
        }
        .profile-info {
            margin-bottom: 1.5rem;
        }
        .profile-info label {
            font-weight: bold;
            color: #6c757d;
        }
        .btn-primary {
            background-color: #e83e8c;
            border-color: #e83e8c;
        }
        .btn-primary:hover {
            background-color: #d63384;
            border-color: #d63384;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Plataforma de Maternidade</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Início</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="perfil.php">Perfil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Sair</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="profile-container">
            <h1 class="profile-header">Perfil do Usuário</h1>
            
            <div class="profile-info">
                <label>Nome:</label>
                <p><?php echo htmlspecialchars($usuario['nome']); ?></p>
            </div>
            
            <div class="profile-info">
                <label>Email:</label>
                <p><?php echo htmlspecialchars($usuario['email']); ?></p>
            </div>
            
            <div class="profile-info">
                <label>Data de Cadastro:</label>
                <p><?php echo date('d/m/Y H:i', strtotime($usuario['created_at'])); ?></p>
            </div>
            
            <div class="profile-info">
                <label>Última Atualização:</label>
                <p><?php echo date('d/m/Y H:i', strtotime($usuario['updated_at'])); ?></p>
            </div>
            
            <div class="text-center">
                <a href="index.php" class="btn btn-primary">Voltar para o Início</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 