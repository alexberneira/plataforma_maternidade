<?php
require_once 'init.php';
require_once 'stripe-config.php';

echo "<h1>🧪 Teste de Configuração do Stripe</h1>";

// Teste 1: Verificar se as chaves estão configuradas
echo "<h2>1. Verificação das Chaves</h2>";
if (STRIPE_PUBLISHABLE_KEY && STRIPE_SECRET_KEY) {
    echo "✅ Chaves configuradas<br>";
    echo "Publishable Key: " . substr(STRIPE_PUBLISHABLE_KEY, 0, 20) . "...<br>";
    echo "Secret Key: " . substr(STRIPE_SECRET_KEY, 0, 20) . "...<br>";
} else {
    echo "❌ Chaves não configuradas<br>";
}

// Teste 2: Verificar Price ID
echo "<h2>2. Verificação do Price ID</h2>";
if (SUBSCRIPTION_PRICE_ID) {
    echo "✅ Price ID configurado: " . SUBSCRIPTION_PRICE_ID . "<br>";
} else {
    echo "❌ Price ID não configurado<br>";
}

// Teste 3: Testar conexão com Stripe
echo "<h2>3. Teste de Conexão com Stripe</h2>";
try {
    require_once 'vendor/autoload.php';
    \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
    
    // Tentar buscar informações da conta
    $account = \Stripe\Account::retrieve();
    echo "✅ Conexão com Stripe estabelecida<br>";
    echo "Account ID: " . $account->id . "<br>";
    echo "Country: " . $account->country . "<br>";
} catch (Exception $e) {
    echo "❌ Erro na conexão com Stripe: " . $e->getMessage() . "<br>";
}

// Teste 4: Verificar se o Price ID existe
echo "<h2>4. Verificação do Price ID no Stripe</h2>";
try {
    if (SUBSCRIPTION_PRICE_ID) {
        $price = \Stripe\Price::retrieve(SUBSCRIPTION_PRICE_ID);
        echo "✅ Price encontrado no Stripe<br>";
        echo "Nome: " . $price->nickname . "<br>";
        echo "Valor: R$ " . ($price->unit_amount / 100) . "<br>";
        echo "Moeda: " . strtoupper($price->currency) . "<br>";
        echo "Tipo: " . $price->type . "<br>";
    }
} catch (Exception $e) {
    echo "❌ Price ID não encontrado no Stripe: " . $e->getMessage() . "<br>";
}

// Teste 5: Verificar banco de dados
echo "<h2>5. Verificação do Banco de Dados</h2>";
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'assinaturas'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Tabela 'assinaturas' existe<br>";
    } else {
        echo "❌ Tabela 'assinaturas' não existe<br>";
    }
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'pagamentos_recorrentes'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Tabela 'pagamentos_recorrentes' existe<br>";
    } else {
        echo "❌ Tabela 'pagamentos_recorrentes' não existe<br>";
    }
} catch (PDOException $e) {
    echo "❌ Erro ao verificar banco de dados: " . $e->getMessage() . "<br>";
}

// Teste 6: Verificar arquivos necessários
echo "<h2>6. Verificação de Arquivos</h2>";
$files = [
    'vendor/autoload.php' => 'Stripe PHP SDK',
    'stripe-config.php' => 'Configuração do Stripe',
    'webhook.php' => 'Webhook handler',
    'assinatura.php' => 'Página de assinatura'
];

foreach ($files as $file => $description) {
    if (file_exists($file)) {
        echo "✅ $description ($file)<br>";
    } else {
        echo "❌ $description ($file) - Arquivo não encontrado<br>";
    }
}

// Teste 7: Verificar permissões
echo "<h2>7. Verificação de Permissões</h2>";
$dirs = [
    'pdfs' => 'Diretório de PDFs',
    'logs' => 'Diretório de logs'
];

foreach ($dirs as $dir => $description) {
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            echo "✅ $description ($dir) - Gravável<br>";
        } else {
            echo "⚠️ $description ($dir) - Não gravável<br>";
        }
    } else {
        echo "❌ $description ($dir) - Diretório não existe<br>";
    }
}

echo "<h2>🎯 Próximos Passos</h2>";
echo "<ol>";
echo "<li>Configure suas chaves reais do Stripe no arquivo stripe-config.php</li>";
echo "<li>Crie um produto e preço no Dashboard do Stripe</li>";
echo "<li>Configure o webhook no Dashboard do Stripe</li>";
echo "<li>Teste o fluxo completo de assinatura</li>";
echo "</ol>";

echo "<p><strong>📖 Consulte o arquivo STRIPE_SETUP_GUIDE.md para instruções detalhadas</strong></p>";
?> 