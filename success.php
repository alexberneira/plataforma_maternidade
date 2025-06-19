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
$trialInfo = null;

// Verificar se foi redirecionado do Stripe
if (isset($_GET['session_id'])) {
    try {
        require_once 'vendor/autoload.php';
        \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
        
        // Recuperar a sessão do Stripe
        $session = \Stripe\Checkout\Session::retrieve($_GET['session_id']);
        
        if ($session->payment_status === 'paid' || $session->mode === 'subscription') {
            $sucesso = "🎉 Parabéns! Seu trial gratuito foi ativado com sucesso!";
            
            // Buscar informações da assinatura
            if ($session->subscription) {
                $subscription = \Stripe\Subscription::retrieve($session->subscription);
                $trialEnd = date('d/m/Y', $subscription->trial_end);
                $trialInfo = "Seu trial gratuito termina em: <strong>$trialEnd</strong>";
            }
            
            // Verificar se a assinatura já foi salva no banco (via webhook)
            $stmt = $pdo->prepare("SELECT * FROM assinaturas WHERE stripe_subscription_id = ?");
            $stmt->execute([$session->subscription]);
            
            if ($stmt->rowCount() == 0) {
                // Salvar assinatura manualmente se o webhook não processou ainda
                $stmt = $pdo->prepare("INSERT INTO assinaturas (usuario_email, stripe_subscription_id, status, valor, created_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->execute([
                    $_SESSION['user_email'],
                    $session->subscription,
                    'trialing',
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
        }

        body {
            background-color: var(--light-bg);
            font-family: 'Poppins', sans-serif;
            color: var(--text-color);
            line-height: 1.6;
        }

        .success-header {
            background: linear-gradient(135deg, var(--success-color), #20c997);
            color: var(--white);
            padding: 4rem 0;
            margin-bottom: 3rem;
            text-align: center;
        }

        .success-header h1 {
            font-weight: 600;
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .success-card {
            background: var(--white);
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
            margin-bottom: 2rem;
        }

        .trial-info-box {
            background: linear-gradient(135deg, var(--accent-color), #FFD93D);
            color: var(--text-color);
            padding: 2rem;
            border-radius: 15px;
            margin: 2rem 0;
        }

        .next-steps {
            background: var(--light-bg);
            padding: 2rem;
            border-radius: 15px;
            margin: 2rem 0;
        }

        .next-steps h3 {
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .step {
            display: flex;
            align-items: center;
            margin: 1rem 0;
            padding: 1rem;
            background: var(--white);
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .step-number {
            background: var(--primary-color);
            color: var(--white);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: 1rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), #FF8E8E);
            border: none;
            color: var(--white);
            padding: 1rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 107, 107, 0.4);
            color: var(--white);
        }

        .alert {
            border-radius: 15px;
            border: none;
            padding: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="success-header">
        <div class="container">
            <h1>🎉 Sucesso!</h1>
            <p class="lead">Seu trial gratuito foi ativado com sucesso</p>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <?php if ($erro): ?>
                    <div class="alert alert-danger">
                        <strong>Erro:</strong> <?php echo htmlspecialchars($erro); ?>
                    </div>
                <?php endif; ?>

                <?php if ($sucesso): ?>
                    <div class="success-card">
                        <div style="font-size: 4rem; margin-bottom: 1rem;">✅</div>
                        <h2><?php echo htmlspecialchars($sucesso); ?></h2>
                        
                        <?php if ($trialInfo): ?>
                            <div class="trial-info-box">
                                <h4>📅 Informações do Trial</h4>
                                <p><?php echo $trialInfo; ?></p>
                                <p><strong>Após o trial:</strong> Você será cobrado automaticamente R$ 39,00/mês</p>
                            </div>
                        <?php endif; ?>

                        <div class="next-steps">
                            <h3>🚀 Próximos Passos</h3>
                            
                            <div class="step">
                                <div class="step-number">1</div>
                                <div>
                                    <strong>Acesse todos os conteúdos</strong><br>
                                    Explore os posts exclusivos sobre maternidade na plataforma
                                </div>
                            </div>
                            
                            <div class="step">
                                <div class="step-number">2</div>
                                <div>
                                    <strong>Disfrute do trial gratuito</strong><br>
                                    Você tem 7 dias para experimentar tudo sem pagar nada
                                </div>
                            </div>
                            
                            <div class="step">
                                <div class="step-number">3</div>
                                <div>
                                    <strong>Cobrança automática</strong><br>
                                    Após 7 dias, será cobrado R$ 39,00 automaticamente
                                </div>
                            </div>
                            
                            <div class="step">
                                <div class="step-number">4</div>
                                <div>
                                    <strong>Cancelamento fácil</strong><br>
                                    Cancele a qualquer momento em seu perfil
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <a href="index.php" class="btn btn-primary">
                                🏠 Ir para a Plataforma
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 