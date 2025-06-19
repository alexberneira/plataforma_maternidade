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
$checkout_url = null;

// Verifica se já tem assinatura ativa
$assinaturaAtiva = hasActiveSubscription($_SESSION['user_email'], $pdo);

if ($assinaturaAtiva) {
    $sucesso = "Você já tem uma assinatura ativa!";
} else {
    // Processa nova assinatura
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        try {
            require_once 'vendor/autoload.php';
            \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
            
            // Criar ou buscar cliente no Stripe
            $stmt = $pdo->prepare("SELECT stripe_customer_id FROM usuarios WHERE email = ?");
            $stmt->execute([$_SESSION['user_email']]);
            $usuario = $stmt->fetch();
            
            if ($usuario && $usuario['stripe_customer_id']) {
                $customerId = $usuario['stripe_customer_id'];
            } else {
                // Criar novo cliente no Stripe
                $customer = \Stripe\Customer::create([
                    'email' => $_SESSION['user_email'],
                    'name' => $_SESSION['user_email'],
                    'metadata' => [
                        'user_email' => $_SESSION['user_email']
                    ]
                ]);
                
                $customerId = $customer->id;
                
                // Salvar customer ID no banco
                $stmt = $pdo->prepare("UPDATE usuarios SET stripe_customer_id = ? WHERE email = ?");
                $stmt->execute([$customerId, $_SESSION['user_email']]);
            }
            
            // Criar sessão de checkout do Stripe
            $checkout_session = \Stripe\Checkout\Session::create([
                'customer' => $customerId,
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price' => SUBSCRIPTION_PRICE_ID,
                    'quantity' => 1,
                ]],
                'mode' => 'subscription',
                'success_url' => 'http://localhost:8080/success.php?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => 'http://localhost:8080/assinatura.php?canceled=true',
                'metadata' => [
                    'user_email' => $_SESSION['user_email']
                ]
            ]);
            
            // Armazenar URL do checkout para redirecionamento via JavaScript
            $checkout_url = $checkout_session->url;
            
        } catch (Exception $e) {
            $erro = "Erro ao processar assinatura: " . $e->getMessage();
        }
    }
}

// Verificar se foi cancelado
if (isset($_GET['canceled'])) {
    $erro = "Assinatura cancelada. Tente novamente quando quiser.";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assinatura - Plataforma de Maternidade</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .subscription-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .subscription-header {
            text-align: center;
            margin-bottom: 2rem;
            color: #e83e8c;
        }
        .price-display {
            font-size: 2.5rem;
            font-weight: bold;
            color: #e83e8c;
            text-align: center;
            margin: 1rem 0;
        }
        .btn-primary {
            background-color: #e83e8c;
            border-color: #e83e8c;
        }
        .btn-primary:hover {
            background-color: #d63384;
            border-color: #d63384;
        }
        .features-list {
            background-color: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin: 1.5rem 0;
        }
        .features-list li {
            margin-bottom: 0.5rem;
            color: #495057;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Plataforma de Maternidade</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">Início</a>
                <a class="nav-link active" href="assinatura.php">Assinatura</a>
                <a class="nav-link" href="logout.php">Sair</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="subscription-container">
            <h1 class="subscription-header">Assinatura Mensal</h1>
            
            <?php if ($erro): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($erro); ?></div>
            <?php endif; ?>
            
            <?php if ($sucesso): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($sucesso); ?></div>
            <?php endif; ?>
            
            <div class="text-center">
                <h4>📚 Acesso Completo aos Materiais</h4>
                <div class="price-display">R$ 49,00/mês</div>
                
                <div class="features-list">
                    <ul class="list-unstyled">
                        <li>✓ Acesso a todos os guias de pré-natal</li>
                        <li>✓ Orientações sobre amamentação</li>
                        <li>✓ Dicas de nutrição durante a gestação</li>
                        <li>✓ Exercícios seguros para gestantes</li>
                        <li>✓ Preparação completa para o parto</li>
                        <li>✓ Atualizações mensais de conteúdo</li>
                        <li>✓ Cancelamento a qualquer momento</li>
                    </ul>
                </div>
                
                <?php if (!$assinaturaAtiva): ?>
                <form method="POST" action="" class="mt-4" id="subscription-form">
                    <button type="submit" class="btn btn-primary btn-lg" id="subscribe-btn">
                        <i class="fas fa-credit-card"></i> Assinar por R$ 49,00/mês
                    </button>
                </form>
                <p class="text-muted mt-2">
                    <small>Pagamento seguro processado pelo Stripe</small>
                </p>
                <?php endif; ?>
            </div>
            
            <div class="text-center mt-3">
                <a href="index.php" class="btn btn-outline-secondary">Voltar para o Início</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <?php if ($checkout_url): ?>
    <script>
        // Redirecionar para o Stripe Checkout
        window.location.href = '<?php echo $checkout_url; ?>';
    </script>
    <?php endif; ?>
</body>
</html> 