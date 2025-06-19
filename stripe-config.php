<?php
// Configurações do Stripe
// ⚠️ IMPORTANTE: Substitua pelas suas chaves reais do Stripe

// Chaves de TESTE (para desenvolvimento)
define('STRIPE_PUBLISHABLE_KEY', 'sua_chave_publica_aqui');
define('STRIPE_SECRET_KEY', 'sua_chave_secreta_aqui');

// Price ID da assinatura no Stripe
define('SUBSCRIPTION_PRICE_ID', 'seu_price_id_aqui');
define('SUBSCRIPTION_AMOUNT', 49.00);
define('SUBSCRIPTION_CURRENCY', 'brl');
define('SUBSCRIPTION_DESCRIPTION', 'Assinatura Plataforma de Maternidade');

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

// Função para criar uma assinatura
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
            'payment_behavior' => 'default_incomplete',
            'payment_settings' => ['save_default_payment_method' => 'on_subscription'],
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

// Função para verificar se um usuário tem assinatura ativa
function hasActiveSubscription($userEmail, $pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM assinaturas WHERE usuario_email = ? AND status = 'active' AND (data_fim IS NULL OR data_fim > NOW())");
        $stmt->execute([$userEmail]);
        return $stmt->fetch() !== false;
    } catch (PDOException $e) {
        error_log('Erro ao verificar assinatura: ' . $e->getMessage());
        return false;
    }
} 