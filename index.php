<?php
require_once 'init.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['user_email'])) {
    header('Location: login.php');
    exit;
}

// Processa o logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Busca os vídeos
$stmt = $pdo->query("SELECT * FROM videos ORDER BY data_cadastro DESC");
$videos = $stmt->fetchAll();

// Busca os PDFs
$stmt = $pdo->query("SELECT * FROM pdfs ORDER BY data_cadastro DESC");
$pdfs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plataforma de Maternidade</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .header {
            background-color: #e83e8c;
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            margin-bottom: 2rem;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card-title {
            color: #e83e8c;
            font-weight: bold;
        }
        .pdf-container {
            height: 600px;
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .nav-link {
            color: #e83e8c;
            font-weight: 500;
        }
        .nav-link:hover {
            color: #d63384;
        }
        .btn-primary {
            background-color: #e83e8c;
            border-color: #e83e8c;
        }
        .btn-primary:hover {
            background-color: #d63384;
            border-color: #d63384;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="#" style="color: #e83e8c; font-weight: bold;">Plataforma de Maternidade</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Início</a>
                    </li>
                    <?php if (isset($_SESSION['user_email'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="perfil.php">Perfil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Sair</a>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="header">
        <div class="container">
            <h1 class="display-4">Bem-vinda à Plataforma de Maternidade</h1>
            <p class="lead">Informações e recursos importantes para sua gestação</p>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Guia Pré-Natal</h5>
                        <p class="card-text">Informações essenciais sobre o acompanhamento pré-natal, exames e cuidados durante a gestação.</p>
                        <iframe src="pdfs/guia_pre_natal.pdf" class="pdf-container w-100"></iframe>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Amamentação: Mitos e Verdades</h5>
                        <p class="card-text">Guia completo sobre amamentação, desmistificando crenças populares e fornecendo informações científicas.</p>
                        <iframe src="pdfs/amamentacao_mitos_verdades.pdf" class="pdf-container w-100"></iframe>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Alimentação na Gestação</h5>
                        <p class="card-text">Dicas de alimentação saudável durante a gestação, incluindo alimentos recomendados e a evitar.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Exercícios para Gestantes</h5>
                        <p class="card-text">Exercícios seguros e recomendados para manter a saúde durante a gestação.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Preparação para o Parto</h5>
                        <p class="card-text">Informações sobre os diferentes tipos de parto e como se preparar para o grande dia.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-white mt-5 py-4">
        <div class="container text-center">
            <p class="text-muted">© 2024 Plataforma de Maternidade. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 