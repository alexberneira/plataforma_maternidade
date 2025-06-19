<?php
require_once 'init.php';
require_once 'stripe-config.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['user_email'])) {
    header('Location: login.php');
    exit;
}

$erro = null;
$sucesso = null;

// Verificar se foi redirecionado do Stripe
if (isset($_GET['session_id'])) {
    try {
        require_once 'vendor/autoload.php';
        \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
        
        // Recuperar a sessão do Stripe
        $session = \Stripe\Checkout\Session::retrieve($_GET['session_id']);
        
        if ($session->payment_status === 'paid') {
            $sucesso = "🎉 Parabéns! Sua assinatura foi ativada com sucesso!";
            
            // Verificar se a assinatura já foi salva no banco (via webhook)
            $stmt = $pdo->prepare("SELECT * FROM assinaturas WHERE stripe_subscription_id = ?");
            $stmt->execute([$session->subscription]);
            
            if ($stmt->rowCount() == 0) {
                // Salvar assinatura manualmente se o webhook não processou ainda
                $stmt = $pdo->prepare("INSERT INTO assinaturas (usuario_email, stripe_subscription_id, status, valor, created_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->execute([
                    $_SESSION['user_email'],
                    $session->subscription,
                    'active',
                    SUBSCRIPTION_AMOUNT
                ]);
            }
        } else {
            $erro = "Pagamento não foi processado. Tente novamente.";
        }
        
    } catch (Exception $e) {
        $erro = "Erro ao verificar pagamento: " . $e->getMessage();
    }
} else {
    $erro = "Sessão inválida.";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sucesso - Plataforma de Maternidade</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .success-container {
            max-width: 600px;
            margin: 3rem auto;
            padding: 2rem;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .success-icon {
            font-size: 4rem;
            color: #28a745;
            margin-bottom: 1rem;
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
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">Início</a>
                <a class="nav-link" href="assinatura.php">Assinatura</a>
                <a class="nav-link" href="logout.php">Sair</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="success-container">
            <?php if ($erro): ?>
                <div class="alert alert-danger">
                    <h4>❌ Erro</h4>
                    <p><?php echo htmlspecialchars($erro); ?></p>
                    <a href="assinatura.php" class="btn btn-primary">Tentar Novamente</a>
                </div>
            <?php endif; ?>
            
            <?php if ($sucesso): ?>
                <div class="success-icon">🎉</div>
                <h1 class="text-success mb-4">Assinatura Ativada!</h1>
                <p class="lead"><?php echo htmlspecialchars($sucesso); ?></p>
                
                <div class="alert alert-info">
                    <h5>📚 O que você tem acesso agora:</h5>
                    <ul class="list-unstyled">
                        <li>✓ Todos os guias de pré-natal</li>
                        <li>✓ Orientações sobre amamentação</li>
                        <li>✓ Dicas de nutrição durante a gestação</li>
                        <li>✓ Exercícios seguros para gestantes</li>
                        <li>✓ Preparação completa para o parto</li>
                    </ul>
                </div>
                
                <div class="mt-4">
                    <a href="index.php" class="btn btn-primary btn-lg">Acessar Conteúdo</a>
                </div>
                
                <div class="mt-3">
                    <small class="text-muted">
                        Sua assinatura será renovada automaticamente todo mês.<br>
                        Você pode cancelar a qualquer momento no seu perfil.
                    </small>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 