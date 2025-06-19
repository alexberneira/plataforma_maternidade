<?php
require_once 'init.php';
require_once 'stripe-config.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['user_email'])) {
    header('Location: login.php');
    exit;
}

// Verifica se já tem assinatura ativa
if (hasActiveSubscription($_SESSION['user_email'], $pdo)) {
    header('Location: index.php');
    exit;
}

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_SESSION['user_email'];
    $nome = $_SESSION['user_nome'] ?? 'Usuário';

    try {
        // Inicializar Stripe
        if (!initStripe()) {
            throw new Exception('Erro ao inicializar Stripe');
        }

        // Criar ou buscar cliente
        $customer = createCustomer($email, $nome);
        if (!$customer) {
            throw new Exception('Erro ao criar cliente no Stripe');
        }

        // Criar Checkout Session para capturar cartão
        $session = \Stripe\Checkout\Session::create([
            'customer' => $customer->id,
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price' => SUBSCRIPTION_PRICE_ID,
                'quantity' => 1,
            ]],
            'mode' => 'subscription',
            'subscription_data' => [
                'trial_period_days' => TRIAL_DAYS,
                'metadata' => [
                    'user_email' => $email,
                    'user_name' => $nome
                ]
            ],
            'success_url' => 'http://localhost:8080/success.php?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => 'http://localhost:8080/assinatura.php?canceled=true',
            'allow_promotion_codes' => true,
            'billing_address_collection' => 'required',
        ]);

        // Redirecionar para o Stripe Checkout
        header('Location: ' . $session->url);
        exit;

    } catch (Exception $e) {
        $erro = 'Erro interno: ' . $e->getMessage();
        error_log('Erro na criação de assinatura: ' . $e->getMessage());
    }
}

// Verificar se foi cancelado
if (isset($_GET['canceled'])) {
    $erro = 'Assinatura cancelada. Tente novamente quando quiser.';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assinatura - Plataforma de Maternidade</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #FF6B6B;
            --secondary-color: #4ECDC4;
            --accent-color: #FFE66D;
            --text-color: #2C3E50;
            --light-bg: #F7F9FC;
            --white: #FFFFFF;
            --success-color: #28a745;
            --warning-color: #ffc107;
        }

        body {
            background-color: var(--light-bg);
            font-family: 'Poppins', sans-serif;
            color: var(--text-color);
            line-height: 1.6;
        }

        .header {
            background: linear-gradient(135deg, var(--primary-color), #FF8E8E);
            color: var(--white);
            padding: 3rem 0;
            margin-bottom: 3rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header h1 {
            font-weight: 600;
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .header p {
            font-weight: 300;
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .pricing-card {
            background: var(--white);
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .trial-badge {
            position: absolute;
            top: 20px;
            right: -30px;
            background: var(--accent-color);
            color: var(--text-color);
            padding: 8px 40px;
            transform: rotate(45deg);
            font-weight: 600;
            font-size: 0.9rem;
        }

        .price {
            font-size: 3rem;
            font-weight: 700;
            color: var(--primary-color);
            margin: 1rem 0;
        }

        .price .currency {
            font-size: 1.5rem;
            vertical-align: top;
        }

        .price .period {
            font-size: 1rem;
            color: var(--text-color);
            opacity: 0.7;
        }

        .trial-info {
            background: linear-gradient(135deg, var(--accent-color), #FFD93D);
            color: var(--text-color);
            padding: 1rem;
            border-radius: 10px;
            margin: 1rem 0;
            font-weight: 500;
        }

        .features {
            list-style: none;
            padding: 0;
            margin: 2rem 0;
        }

        .features li {
            padding: 0.5rem 0;
            border-bottom: 1px solid #eee;
        }

        .features li:before {
            content: "✅";
            margin-right: 10px;
        }

        .btn-trial {
            background: linear-gradient(135deg, var(--primary-color), #FF8E8E);
            border: none;
            color: var(--white);
            padding: 1rem 3rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
        }

        .btn-trial:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 107, 107, 0.4);
            color: var(--white);
        }

        .security-info {
            background: var(--light-bg);
            padding: 1rem;
            border-radius: 10px;
            margin-top: 2rem;
            font-size: 0.9rem;
            color: var(--text-color);
            opacity: 0.8;
        }

        .alert {
            border-radius: 15px;
            border: none;
            padding: 1rem 1.5rem;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>🎁 Trial Gratuito</h1>
            <p>Experimente nossa plataforma por 7 dias sem compromisso</p>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <?php if ($erro): ?>
                    <div class="alert alert-danger">
                        <strong>Erro:</strong> <?php echo htmlspecialchars($erro); ?>
                    </div>
                <?php endif; ?>

                <?php if ($sucesso): ?>
                    <div class="alert alert-success">
                        <strong>Sucesso:</strong> <?php echo htmlspecialchars($sucesso); ?>
                    </div>
                <?php endif; ?>

                <div class="pricing-card">
                    <div class="trial-badge">TRIAL</div>
                    
                    <h2>Plano Completo</h2>
                    
                    <div class="price">
                        <span class="currency">R$</span>0
                        <span class="period">/7 dias</span>
                    </div>
                    
                    <div class="trial-info">
                        <strong>🎉 7 dias de acesso gratuito!</strong><br>
                        Depois apenas R$ 39,00/mês
                    </div>

                    <ul class="features">
                        <li>Posts exclusivos sobre maternidade</li>
                        <li>Conteúdo atualizado</li>
                        <li>Suporte especializado</li>
                        <li>Cancelamento a qualquer momento</li>
                    </ul>

                    <form method="POST">
                        <button type="submit" class="btn btn-trial">
                            🚀 Começar Trial Gratuito
                        </button>
                    </form>

                    <div class="security-info">
                        <strong>🔒 Segurança:</strong> Seus dados estão protegidos pelo Stripe. 
                        Você só será cobrado após os 7 dias de trial, e pode cancelar a qualquer momento.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 