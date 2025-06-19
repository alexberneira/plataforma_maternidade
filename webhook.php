<?php
require_once 'init.php';
require_once 'stripe-config.php';

// ⚠️ IMPORTANTE: Configure o webhook no Stripe
// Dashboard Stripe > Developers > Webhooks > Add endpoint
// URL: https://seudominio.com/webhook.php
// Eventos: customer.subscription.created, customer.subscription.updated, customer.subscription.deleted, invoice.payment_succeeded, invoice.payment_failed

// Configuração do webhook - Substitua pelo seu Signing Secret
$endpoint_secret = 'whsec_XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX';

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

// Manipula o evento
switch ($event->type) {
    case 'customer.subscription.created':
        $subscription = $event->data->object;
        
        // Salva a assinatura no banco de dados
        try {
            $stmt = $pdo->prepare("INSERT INTO assinaturas (usuario_email, stripe_subscription_id, status, valor) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE status = ?, updated_at = NOW()");
            $stmt->execute([
                $subscription->customer_email ?? 'N/A',
                $subscription->id,
                $subscription->status,
                SUBSCRIPTION_AMOUNT,
                $subscription->status
            ]);
        } catch(PDOException $e) {
            error_log('Erro ao salvar assinatura: ' . $e->getMessage());
        }
        break;
        
    case 'customer.subscription.updated':
        $subscription = $event->data->object;
        
        // Atualiza o status da assinatura
        try {
            $stmt = $pdo->prepare("UPDATE assinaturas SET status = ?, updated_at = NOW() WHERE stripe_subscription_id = ?");
            $stmt->execute([$subscription->status, $subscription->id]);
        } catch(PDOException $e) {
            error_log('Erro ao atualizar assinatura: ' . $e->getMessage());
        }
        break;
        
    case 'customer.subscription.deleted':
        $subscription = $event->data->object;
        
        // Marca a assinatura como cancelada
        try {
            $stmt = $pdo->prepare("UPDATE assinaturas SET status = 'canceled', data_fim = NOW(), updated_at = NOW() WHERE stripe_subscription_id = ?");
            $stmt->execute([$subscription->id]);
        } catch(PDOException $e) {
            error_log('Erro ao cancelar assinatura: ' . $e->getMessage());
        }
        break;
        
    case 'invoice.payment_succeeded':
        $invoice = $event->data->object;
        
        // Salva o pagamento recorrente
        try {
            $stmt = $pdo->prepare("INSERT INTO pagamentos_recorrentes (stripe_invoice_id, stripe_subscription_id, usuario_email, valor, status, data_vencimento, data_pagamento) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([
                $invoice->id,
                $invoice->subscription,
                $invoice->customer_email ?? 'N/A',
                $invoice->amount_paid / 100, // Converte de centavos para reais
                'paid',
                date('Y-m-d', $invoice->due_date)
            ]);
        } catch(PDOException $e) {
            error_log('Erro ao salvar pagamento: ' . $e->getMessage());
        }
        break;
        
    case 'invoice.payment_failed':
        $invoice = $event->data->object;
        
        // Salva o pagamento falhado
        try {
            $stmt = $pdo->prepare("INSERT INTO pagamentos_recorrentes (stripe_invoice_id, stripe_subscription_id, usuario_email, valor, status, data_vencimento) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $invoice->id,
                $invoice->subscription,
                $invoice->customer_email ?? 'N/A',
                $invoice->amount_due / 100,
                'uncollectible',
                date('Y-m-d', $invoice->due_date)
            ]);
        } catch(PDOException $e) {
            error_log('Erro ao salvar pagamento falhado: ' . $e->getMessage());
        }
        break;
        
    default:
        // Evento não reconhecido
        echo 'Received unknown event type ' . $event->type;
}

http_response_code(200);
?> 