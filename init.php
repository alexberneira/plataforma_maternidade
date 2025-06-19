<?php
// Inicia a sessão ANTES de qualquer saída
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configurações de encoding
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
mb_regex_encoding('UTF-8');

// Configurações do banco de dados
$host = 'db';  // Nome do serviço no docker-compose
$dbname = 'plataforma_maternidade';
$username = 'root';
$password = 'root';

try {
    // Conexão com o banco de dados usando PDO
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ]
    );
    
    // Força o charset da conexão
    $pdo->exec("SET CHARACTER SET utf8mb4");
    $pdo->exec("SET NAMES utf8mb4");
    $pdo->exec("SET collation_connection = utf8mb4_unicode_ci");
} catch(PDOException $e) {
    // Em caso de erro, armazena a mensagem na sessão e redireciona para a página de erro
    $_SESSION['error'] = "Erro ao conectar com o banco de dados: " . $e->getMessage();
    header('Location: error.php');
    exit;
}

// Configurações gerais
date_default_timezone_set('America/Sao_Paulo');
setlocale(LC_ALL, 'pt_BR.utf-8', 'pt_BR', 'Portuguese_Brazil'); 