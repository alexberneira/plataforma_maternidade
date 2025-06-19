<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste Local - Plataforma Maternidade</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .status { padding: 10px; margin: 10px 0; border-radius: 5px; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info { background-color: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
    </style>
</head>
<body>
    <h1>🧪 Teste Local - Plataforma Maternidade</h1>
    
    <div class="status success">
        ✅ <strong>Servidor PHP funcionando!</strong><br>
        Data/Hora: <?php echo date('d/m/Y H:i:s'); ?>
    </div>

    <?php
    // Teste de conexão com banco de dados
    try {
        require_once 'init.php';
        $pdo = new PDO("mysql:host=db;dbname=plataforma_maternidade", "root", "root");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo '<div class="status success">✅ <strong>Banco de dados conectado!</strong></div>';
        
        // Verificar tabelas
        $tables = ['usuarios', 'assinaturas', 'pagamentos_recorrentes', 'posts'];
        foreach ($tables as $table) {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() > 0) {
                echo '<div class="status success">✅ Tabela <strong>' . $table . '</strong> existe</div>';
            } else {
                echo '<div class="status error">❌ Tabela <strong>' . $table . '</strong> não encontrada</div>';
            }
        }
        
    } catch (PDOException $e) {
        echo '<div class="status error">❌ <strong>Erro no banco de dados:</strong> ' . $e->getMessage() . '</div>';
    }
    
    // Teste de configuração do Stripe
    if (file_exists('stripe-config.php')) {
        echo '<div class="status success">✅ <strong>Arquivo de configuração do Stripe encontrado</strong></div>';
        
        // Verificar se as dependências estão instaladas
        if (file_exists('vendor/autoload.php')) {
            echo '<div class="status success">✅ <strong>Dependências do Stripe instaladas</strong></div>';
        } else {
            echo '<div class="status error">❌ <strong>Dependências do Stripe não encontradas</strong></div>';
        }
    } else {
        echo '<div class="status error">❌ <strong>Arquivo de configuração do Stripe não encontrado</strong></div>';
    }
    
    // Teste de arquivos principais
    $files = ['index.php', 'login.php', 'assinatura.php', 'webhook.php'];
    foreach ($files as $file) {
        if (file_exists($file)) {
            echo '<div class="status success">✅ Arquivo <strong>' . $file . '</strong> existe</div>';
        } else {
            echo '<div class="status error">❌ Arquivo <strong>' . $file . '</strong> não encontrado</div>';
        }
    }
    ?>
    
    <div class="status info">
        📋 <strong>Próximos passos:</strong><br>
        1. Configure suas chaves do Stripe em <code>stripe-config.php</code><br>
        2. Acesse <a href="http://localhost:8080">http://localhost:8080</a><br>
        3. Faça login com: <strong>teste@teste.com</strong> / <strong>123456</strong><br>
        4. Teste a funcionalidade de assinatura
    </div>
    
    <div class="status info">
        🔗 <strong>Links úteis:</strong><br>
        • <a href="http://localhost:8080">Página Principal</a><br>
        • <a href="http://localhost:8080/login.php">Login</a><br>
        • <a href="http://localhost:8080/assinatura.php">Assinatura</a><br>
        • <a href="http://localhost:8080/test-stripe.php">Teste Stripe</a>
    </div>
</body>
</html> 