<?php
session_start();
require_once 'init.php';

// Se já estiver logado, redireciona para a página principal
if (isset($_SESSION['user_email'])) {
    header('Location: index.php');
    exit;
}

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';

    try {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ? AND ativo = TRUE");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        if ($usuario) {
            $_SESSION['user_email'] = $usuario['email'];
            $_SESSION['user_nome'] = $usuario['nome'];
            header('Location: index.php');
            exit;
        } else {
            $erro = "Email não encontrado ou usuário inativo";
        }
    } catch(PDOException $e) {
        $erro = "Erro ao conectar com o banco de dados";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Plataforma de Maternidade</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            max-width: 400px;
            width: 100%;
            padding: 2rem;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .login-title {
            color: #e83e8c;
            text-align: center;
            margin-bottom: 2rem;
        }
        .btn-primary {
            background-color: #e83e8c;
            border-color: #e83e8c;
            width: 100%;
        }
        .btn-primary:hover {
            background-color: #d63384;
            border-color: #d63384;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1 class="login-title">Login</h1>
        <?php if (isset($erro)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($erro); ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <button type="submit" class="btn btn-primary">Entrar</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 