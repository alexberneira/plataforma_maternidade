<?php
require_once 'init.php';
require_once 'stripe-config.php';

// ⚠️ IMPORTANTE: Configure o webhook no Stripe
// Dashboard Stripe > Developers > Webhooks > Add endpoint
// URL: https://seudominio.com/webhook.php
// Eventos: customer.subscription.created, customer.subscription.updated, customer.subscription.deleted, invoice.payment_succeeded, invoice.payment_failed

// Configuração do webhook
$endpoint_secret = 'whsec_SEU_SIGNING_SECRET_AQUI'; // Substitua pelo seu signing secret

$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
$event = null;

try {
    $event = \Stripe\Webhook::constructEvent(
        $payload, $sig_header, $endpoint_secret
    );
} catch(\UnexpectedValueException $e) {
    // Payload inválido
    http_response_code(400);
    exit();
} catch(\Stripe\Exception\SignatureVerificationException $e) {
    // Assinatura inválida
    http_response_code(400);
    exit();
}

// Processa o evento
switch ($event->type) {
    case 'customer.subscription.created':
        $subscription = $event->data->object;
        handleSubscriptionCreated($subscription, $pdo);
        break;
        
    case 'customer.subscription.updated':
        $subscription = $event->data->object;
        handleSubscriptionUpdated($subscription, $pdo);
        break;
        
    case 'customer.subscription.deleted':
        $subscription = $event->data->object;
        handleSubscriptionDeleted($subscription, $pdo);
        break;
        
    case 'invoice.payment_succeeded':
        $invoice = $event->data->object;
        handlePaymentSucceeded($invoice, $pdo);
        break;
        
    case 'invoice.payment_failed':
        $invoice = $event->data->object;
        handlePaymentFailed($invoice, $pdo);
        break;
        
    default:
        // Evento não reconhecido
        echo 'Received unknown event type ' . $event->type;
}

http_response_code(200);

function handleSubscriptionCreated($subscription, $pdo) {
    try {
        $customerId = $subscription->customer;
        $subscriptionId = $subscription->id;
        $status = $subscription->status;
        
        // Busca o email do usuário
        $stmt = $pdo->prepare("SELECT usuario_email FROM assinaturas WHERE stripe_customer_id = ?");
        $stmt->execute([$customerId]);
        $assinatura = $stmt->fetch();
        
        if ($assinatura) {
            $email = $assinatura['usuario_email'];
            
            // Atualiza o status da assinatura
            $stmt = $pdo->prepare("UPDATE assinaturas SET status = ? WHERE stripe_subscription_id = ?");
            $stmt->execute([$status, $subscriptionId]);
            
            // Se for trial, define a data de fim do trial
            if ($status === 'trialing' && $subscription->trial_end) {
                $trialEnd = date('Y-m-d H:i:s', $subscription->trial_end);
                $stmt = $pdo->prepare("UPDATE assinaturas SET trial_end = ?, data_fim = ? WHERE stripe_subscription_id = ?");
                $stmt->execute([$trialEnd, $trialEnd, $subscriptionId]);
            }
            
            error_log("Assinatura criada: $email - Status: $status");
        }
    } catch (Exception $e) {
        error_log('Erro ao processar subscription.created: ' . $e->getMessage());
    }
}

function handleSubscriptionUpdated($subscription, $pdo) {
    try {
        $subscriptionId = $subscription->id;
        $status = $subscription->status;
        
        // Atualiza o status da assinatura
        $stmt = $pdo->prepare("UPDATE assinaturas SET status = ? WHERE stripe_subscription_id = ?");
        $stmt->execute([$status, $subscriptionId]);
        
        // Se o trial terminou e a assinatura está ativa, atualiza a data de fim
        if ($status === 'active' && $subscription->current_period_end) {
            $periodEnd = date('Y-m-d H:i:s', $subscription->current_period_end);
            $stmt = $pdo->prepare("UPDATE assinaturas SET data_fim = ? WHERE stripe_subscription_id = ?");
            $stmt->execute([$periodEnd, $subscriptionId]);
        }
        
        error_log("Assinatura atualizada: $subscriptionId - Status: $status");
    } catch (Exception $e) {
        error_log('Erro ao processar subscription.updated: ' . $e->getMessage());
    }
}

function handleSubscriptionDeleted($subscription, $pdo) {
    try {
        $subscriptionId = $subscription->id;
        
        // Marca a assinatura como cancelada
        $stmt = $pdo->prepare("UPDATE assinaturas SET status = 'canceled' WHERE stripe_subscription_id = ?");
        $stmt->execute([$subscriptionId]);
        
        error_log("Assinatura cancelada: $subscriptionId");
    } catch (Exception $e) {
        error_log('Erro ao processar subscription.deleted: ' . $e->getMessage());
    }
}

function handlePaymentSucceeded($invoice, $pdo) {
    try {
        $subscriptionId = $invoice->subscription;
        $amount = $invoice->amount_paid / 100; // Converte de centavos para reais
        
        // Registra o pagamento
        $stmt = $pdo->prepare("INSERT INTO pagamentos_recorrentes (stripe_subscription_id, valor, status, data_pagamento) VALUES (?, ?, 'succeeded', NOW())");
        $stmt->execute([$subscriptionId, $amount]);
        
        // Atualiza a data de fim da assinatura
        if ($invoice->period_end) {
            $periodEnd = date('Y-m-d H:i:s', $invoice->period_end);
            $stmt = $pdo->prepare("UPDATE assinaturas SET data_fim = ? WHERE stripe_subscription_id = ?");
            $stmt->execute([$periodEnd, $subscriptionId]);
        }
        
        error_log("Pagamento realizado: $subscriptionId - Valor: R$ $amount");
    } catch (Exception $e) {
        error_log('Erro ao processar payment.succeeded: ' . $e->getMessage());
    }
}

function handlePaymentFailed($invoice, $pdo) {
    try {
        $subscriptionId = $invoice->subscription;
        $amount = $invoice->amount_due / 100;
        
        // Registra a tentativa de pagamento falhada
        $stmt = $pdo->prepare("INSERT INTO pagamentos_recorrentes (stripe_subscription_id, valor, status, data_pagamento) VALUES (?, ?, 'failed', NOW())");
        $stmt->execute([$subscriptionId, $amount]);
        
        error_log("Pagamento falhou: $subscriptionId - Valor: R$ $amount");
    } catch (Exception $e) {
        error_log('Erro ao processar payment.failed: ' . $e->getMessage());
    }
}
?> 