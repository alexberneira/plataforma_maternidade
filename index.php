<?php
// Configurações de encoding
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
mb_regex_encoding('UTF-8');

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

// Busca os PDFs
$stmt = $pdo->query("SELECT * FROM pdfs ORDER BY created_at DESC");
$pdfs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Função para limpar e converter texto
function cleanText($text) {
    // Converte para UTF-8 se não estiver
    if (!mb_check_encoding($text, 'UTF-8')) {
        $text = mb_convert_encoding($text, 'UTF-8', 'auto');
    }
    // Remove caracteres inválidos
    $text = preg_replace('/[\x00-\x1F\x7F]/u', '', $text);
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plataforma de Maternidade</title>
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
            --card-border: rgba(147, 112, 219, 0.15);
        }

        body {
            background-color: var(--light-bg);
            font-family: 'Poppins', sans-serif;
            color: var(--text-color);
            line-height: 1.6;
        }

        .header {
            background: linear-gradient(135deg, var(--primary-color), #FF8E8E);
            color: var(--white);
            padding: 3rem 0;
            margin-bottom: 3rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header h1 {
            font-weight: 600;
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .header p {
            font-weight: 300;
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .navbar {
            background-color: var(--white) !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            padding: 1rem 0;
        }

        .navbar-brand {
            color: var(--primary-color) !important;
            font-weight: 600;
            font-size: 1.5rem;
        }

        .nav-link {
            color: var(--text-color) !important;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .nav-link:hover {
            color: var(--primary-color) !important;
        }

        .card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border: 1px solid rgba(147, 112, 219, 0.15);
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .card h3 {
            color: #2D3748;
            font-size: 1.4rem;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .card p {
            color: #718096;
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 20px;
            flex-grow: 1;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
            border-color: rgba(147, 112, 219, 0.25);
        }

        .card-body {
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .card-title {
            color: var(--primary-color);
            font-weight: 600;
            font-size: 1.25rem;
            margin-bottom: 1rem;
        }

        .card-text {
            color: var(--text-color);
            font-weight: 300;
            margin-bottom: 1.5rem;
            flex: 1;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: auto;
        }

        .btn-primary:hover {
            background-color: #FF5252;
            border-color: #FF5252;
            transform: translateY(-2px);
        }

        .section-title {
            color: var(--text-color);
            font-weight: 600;
            font-size: 2rem;
            margin-bottom: 2rem;
            position: relative;
            padding-bottom: 0.5rem;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background-color: var(--primary-color);
        }

        .pdf-container {
            height: 600px;
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .modal-content {
            border-radius: 15px;
            border: none;
        }

        .modal-header {
            background-color: var(--primary-color);
            color: var(--white);
            border-radius: 15px 15px 0 0;
        }

        .modal-title {
            font-weight: 500;
        }

        .btn-close {
            filter: brightness(0) invert(1);
        }

        footer {
            background-color: var(--white);
            padding: 2rem 0;
            margin-top: 4rem;
            box-shadow: 0 -2px 4px rgba(0, 0, 0, 0.05);
        }

        footer p {
            color: var(--text-color);
            font-weight: 300;
            margin: 0;
        }

        .color-demo {
            background: white;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .color-preview {
            height: 120px;
            border-radius: 8px;
            margin-bottom: 10px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }

        .color-preview .color-name {
            font-size: 1.2rem;
            font-weight: 500;
            margin-bottom: 5px;
            color: white;
        }

        .color-preview .color-code {
            font-size: 0.9rem;
            color: white;
            font-family: monospace;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="#">Maternidade Conectada</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="?logout=1">Sair</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="header">
        <div class="container">
            <h1>Bem-vinda à Maternidade Conectada</h1>
            <p>Seu espaço de apoio e informação para uma gestação saudável e tranquila</p>
        </div>
    </div>

    <div class="container">
        <h2 class="section-title">Materiais de Apoio</h2>
        <div class="row">
            <?php foreach ($pdfs as $pdf): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo cleanText($pdf['titulo']); ?></h5>
                        <p class="card-text"><?php echo cleanText($pdf['descricao']); ?></p>
                        <button class="btn btn-primary" onclick="openPdfModal('pdfs/<?php echo cleanText($pdf['arquivo']); ?>', '<?php echo cleanText($pdf['titulo']); ?>')">Acessar Material</button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Modal para PDFs -->
    <div class="modal fade" id="pdfModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <iframe src="" class="pdf-container w-100"></iframe>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="container text-center">
            <p>© 2024 Maternidade Conectada. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openPdfModal(url, title) {
            const modal = new bootstrap.Modal(document.getElementById('pdfModal'));
            document.querySelector('#pdfModal .modal-title').textContent = title;
            document.querySelector('#pdfModal iframe').src = url;
            modal.show();
        }

        document.getElementById('pdfModal').addEventListener('hidden.bs.modal', function () {
            document.querySelector('#pdfModal iframe').src = '';
        });
    </script>
</body>
</html> 

