<?php
// Configurações do Stripe
// ⚠️ IMPORTANTE: Configure as chaves no arquivo .env

// Função para carregar variáveis de ambiente
function loadEnv($file = '.env') {
    if (!file_exists($file)) {
        return false;
    }
    
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) continue; // Ignora linhas vazias e comentários
        
        if (strpos($line, '=') === false) continue; // Ignora linhas sem '='
        
        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) continue; // Ignora linhas malformadas
        
        $key = trim($parts[0]);
        $value = trim($parts[1]);
        
        if (!empty($key)) {
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
    return true;
}

// Carrega as variáveis de ambiente
loadEnv();

// Chaves do Stripe (do arquivo .env)
define('STRIPE_PUBLISHABLE_KEY', $_ENV['STRIPE_PUBLISHABLE_KEY'] ?? getenv('STRIPE_PUBLISHABLE_KEY') ?? 'sua_chave_publica_aqui');
define('STRIPE_SECRET_KEY', $_ENV['STRIPE_SECRET_KEY'] ?? getenv('STRIPE_SECRET_KEY') ?? 'sua_chave_secreta_aqui');

// Price ID da assinatura no Stripe (com trial)
// ⚠️ IMPORTANTE: Crie um produto no Stripe Dashboard com preço mensal de R$ 39,00
// Depois copie o Price ID (começa com 'price_') e cole aqui:
define('SUBSCRIPTION_PRICE_ID', $_ENV['SUBSCRIPTION_PRICE_ID'] ?? getenv('SUBSCRIPTION_PRICE_ID') ?? 'seu_price_id_aqui');
define('SUBSCRIPTION_AMOUNT', 39.00); // Preço após trial
define('SUBSCRIPTION_CURRENCY', 'brl');
define('SUBSCRIPTION_DESCRIPTION', 'Assinatura Plataforma de Maternidade');
define('TRIAL_DAYS', 7); // Trial gratuito de 7 dias

// Função para inicializar o Stripe
function initStripe() {
    require_once 'vendor/autoload.php';
    try {
        \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
        return true;
    } catch (Exception $e) {
        error_log('Erro ao inicializar Stripe: ' . $e->getMessage());
        return false;
    }
}

// Função para criar uma assinatura com trial
function createSubscription($customerId, $priceId = null) {
    if (!initStripe()) {
        return false;
    }
    try {
        $subscription = \Stripe\Subscription::create([
            'customer' => $customerId,
            'items' => [
                ['price' => $priceId ?: SUBSCRIPTION_PRICE_ID],
            ],
            'trial_period_days' => TRIAL_DAYS,
            'payment_behavior' => 'default_incomplete',
            'payment_settings' => [
                'save_default_payment_method' => 'on_subscription',
                'payment_method_types' => ['card'],
            ],
            'expand' => ['latest_invoice.payment_intent'],
        ]);
        return $subscription;
    } catch (Exception $e) {
        error_log('Erro ao criar assinatura: ' . $e->getMessage());
        return false;
    }
}

// Função para criar um cliente
function createCustomer($email, $name) {
    if (!initStripe()) {
        return false;
    }
    try {
        $customer = \Stripe\Customer::create([
            'email' => $email,
            'name' => $name,
        ]);
        return $customer;
    } catch (Exception $e) {
        error_log('Erro ao criar cliente: ' . $e->getMessage());
        return false;
    }
}

// Função para buscar uma assinatura
function getSubscription($subscriptionId) {
    if (!initStripe()) {
        return false;
    }
    try {
        $subscription = \Stripe\Subscription::retrieve($subscriptionId);
        return $subscription;
    } catch (Exception $e) {
        error_log('Erro ao buscar assinatura: ' . $e->getMessage());
        return false;
    }
}

// Função para cancelar uma assinatura
function cancelSubscription($subscriptionId) {
    if (!initStripe()) {
        return false;
    }
    try {
        $subscription = \Stripe\Subscription::retrieve($subscriptionId);
        $subscription->cancel_at_period_end = true;
        $subscription->save();
        return $subscription;
    } catch (Exception $e) {
        error_log('Erro ao cancelar assinatura: ' . $e->getMessage());
        return false;
    }
}

// Função para verificar se um usuário tem assinatura ativa (incluindo trial)
function hasActiveSubscription($userEmail, $pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM assinaturas WHERE usuario_email = ? AND (status = 'active' OR status = 'trialing') AND (data_fim IS NULL OR data_fim > NOW())");
        $stmt->execute([$userEmail]);
        return $stmt->fetch() !== false;
    } catch (PDOException $e) {
        error_log('Erro ao verificar assinatura: ' . $e->getMessage());
        return false;
    }
}

// Função para verificar se está em trial
function isInTrial($userEmail, $pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM assinaturas WHERE usuario_email = ? AND status = 'trialing' AND (data_fim IS NULL OR data_fim > NOW())");
        $stmt->execute([$userEmail]);
        return $stmt->fetch() !== false;
    } catch (PDOException $e) {
        error_log('Erro ao verificar trial: ' . $e->getMessage());
        return false;
    }
}

// Função para obter dias restantes do trial
function getTrialDaysLeft($userEmail, $pdo) {
    try {
        $stmt = $pdo->prepare("SELECT data_fim FROM assinaturas WHERE usuario_email = ? AND status = 'trialing' AND (data_fim IS NULL OR data_fim > NOW())");
        $stmt->execute([$userEmail]);
        $assinatura = $stmt->fetch();
        
        if ($assinatura && $assinatura['data_fim']) {
            $dataFim = new DateTime($assinatura['data_fim']);
            $agora = new DateTime();
            $diferenca = $agora->diff($dataFim);
            return max(0, $diferenca->days);
        }
        return 0;
    } catch (PDOException $e) {
        error_log('Erro ao calcular dias do trial: ' . $e->getMessage());
        return 0;
    }
} 