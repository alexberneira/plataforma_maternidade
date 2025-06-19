<?php
require_once 'init.php';
require_once 'stripe-config.php';

echo "<h1>🔍 Debug Detalhado do Stripe</h1>";

// Teste 1: Verificar se as constantes estão definidas
echo "<h2>1. Verificação das Constantes</h2>";
echo "STRIPE_PUBLISHABLE_KEY definida: " . (defined('STRIPE_PUBLISHABLE_KEY') ? '✅ SIM' : '❌ NÃO') . "<br>";
echo "STRIPE_SECRET_KEY definida: " . (defined('STRIPE_SECRET_KEY') ? '✅ SIM' : '❌ NÃO') . "<br>";

if (defined('STRIPE_PUBLISHABLE_KEY')) {
    echo "Publishable Key: " . substr(STRIPE_PUBLISHABLE_KEY, 0, 20) . "...<br>";
    echo "Tamanho: " . strlen(STRIPE_PUBLISHABLE_KEY) . " caracteres<br>";
    echo "Começa com pk_live_: " . (strpos(STRIPE_PUBLISHABLE_KEY, 'pk_live_') === 0 ? '✅ SIM' : '❌ NÃO') . "<br>";
}

if (defined('STRIPE_SECRET_KEY')) {
    echo "Secret Key: " . substr(STRIPE_SECRET_KEY, 0, 20) . "...<br>";
    echo "Tamanho: " . strlen(STRIPE_SECRET_KEY) . " caracteres<br>";
    echo "Começa com sk_live_: " . (strpos(STRIPE_SECRET_KEY, 'sk_live_') === 0 ? '✅ SIM' : '❌ NÃO') . "<br>";
}

// Teste 2: Verificar se o Stripe SDK está carregado
echo "<h2>2. Verificação do Stripe SDK</h2>";
if (file_exists('vendor/autoload.php')) {
    echo "✅ vendor/autoload.php existe<br>";
    require_once 'vendor/autoload.php';
    echo "✅ Stripe SDK carregado<br>";
} else {
    echo "❌ vendor/autoload.php não encontrado<br>";
    exit;
}

// Teste 3: Testar conexão com Stripe
echo "<h2>3. Teste de Conexão com Stripe</h2>";
try {
    \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
    echo "✅ Chave definida no Stripe<br>";
    
    // Tentar buscar informações da conta
    $account = \Stripe\Account::retrieve();
    echo "✅ Conexão com Stripe estabelecida<br>";
    echo "Account ID: " . $account->id . "<br>";
    echo "Country: " . $account->country . "<br>";
    echo "Charges enabled: " . ($account->charges_enabled ? '✅ SIM' : '❌ NÃO') . "<br>";
    echo "Payouts enabled: " . ($account->payouts_enabled ? '✅ SIM' : '❌ NÃO') . "<br>";
    
} catch (\Stripe\Exception\AuthenticationException $e) {
    echo "❌ Erro de autenticação: " . $e->getMessage() . "<br>";
    echo "Código: " . $e->getStripeCode() . "<br>";
} catch (\Stripe\Exception\ApiConnectionException $e) {
    echo "❌ Erro de conexão: " . $e->getMessage() . "<br>";
} catch (\Stripe\Exception\ApiErrorException $e) {
    echo "❌ Erro da API: " . $e->getMessage() . "<br>";
    echo "Código: " . $e->getStripeCode() . "<br>";
} catch (Exception $e) {
    echo "❌ Erro geral: " . $e->getMessage() . "<br>";
}

// Teste 4: Verificar se há produtos criados
echo "<h2>4. Verificação de Produtos</h2>";
try {
    $products = \Stripe\Product::all(['limit' => 5]);
    echo "✅ Produtos encontrados: " . count($products->data) . "<br>";
    
    foreach ($products->data as $product) {
        echo "- " . $product->name . " (ID: " . $product->id . ")<br>";
    }
} catch (Exception $e) {
    echo "❌ Erro ao buscar produtos: " . $e->getMessage() . "<br>";
}

// Teste 5: Verificar se há preços criados
echo "<h2>5. Verificação de Preços</h2>";
try {
    $prices = \Stripe\Price::all(['limit' => 5]);
    echo "✅ Preços encontrados: " . count($prices->data) . "<br>";
    
    foreach ($prices->data as $price) {
        echo "- " . ($price->nickname ?: 'Sem nome') . " (ID: " . $price->id . ") - R$ " . ($price->unit_amount / 100) . "/" . $price->recurring->interval . "<br>";
    }
} catch (Exception $e) {
    echo "❌ Erro ao buscar preços: " . $e->getMessage() . "<br>";
}

echo "<h2>🎯 Recomendações</h2>";
echo "<ol>";
echo "<li>Verifique se sua conta Stripe está ativa e verificada</li>";
echo "<li>Confirme se as chaves foram copiadas corretamente</li>";
echo "<li>Crie um produto e preço no Dashboard do Stripe</li>";
echo "<li>Teste com cartões de teste primeiro</li>";
echo "</ol>";
?> 