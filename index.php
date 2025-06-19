<?php
require_once 'init.php';
require_once 'stripe-config.php';

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

// Busca todos os posts
$stmt = $pdo->query("SELECT * FROM posts ORDER BY created_at DESC");
$posts = $stmt->fetchAll();

// Verifica se o usuário tem assinatura ativa
$temAssinatura = false;
if (isset($_SESSION['user_email'])) {
    $temAssinatura = hasActiveSubscription($_SESSION['user_email'], $pdo);
}

// Função para limpar texto
function cleanText($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

// Configurações de encoding
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
mb_regex_encoding('UTF-8');
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
                        <a class="nav-link" href="index.php">Início</a>
                    </li>
                    <?php if (isset($_SESSION['user_email'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="perfil.php">Perfil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin-posts.php">Novo Post</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="assinatura.php">Assinatura</a>
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
            <h1>🚀 Bem-vinda à Maternidade Conectada</h1>
            <p><strong>O segredo das mães que vivem uma gestação tranquila, segura e cheia de informação está aqui.</strong></p>
            <div class="mt-4">
                <h3 style="color: var(--white); font-weight: bold; text-shadow: 0 2px 8px rgba(0,0,0,0.12);">Acesso VIP por apenas R$ 39/mês após 7 dias grátis</h3>
                <p><span style="color: var(--white); font-weight: 500; text-shadow: 0 2px 8px rgba(0,0,0,0.12);">Transforme sua experiência de maternidade com conteúdos exclusivos que você não encontra no Google!</span></p>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if (isset($_SESSION['user_email']) && $temAssinatura): ?>
        <div class="subscription-banner">
            <h4><i class="fas fa-check-circle"></i> Assinatura Ativa</h4>
            <p>Parabéns! Você faz parte do grupo seleto de mães que têm acesso total ao melhor conteúdo de maternidade do Brasil. Aproveite!</p>
        </div>
        <?php endif; ?>

        <?php if (!$temAssinatura): ?>
            <div class="alert alert-warning" role="alert" style="font-size:1.1rem;">
                <h5 class="alert-heading">🔒 Conteúdo Restrito</h5>
                <p><strong>Você está a um passo de desbloquear TODO o conhecimento que pode transformar sua maternidade.</strong></p>
                <ul>
                  <li>🎁 <b>7 dias grátis</b> para acessar tudo sem compromisso</li>
                  <li>💎 Materiais exclusivos, atualizados e práticos</li>
                  <li>👩‍⚕️ Suporte de especialistas e comunidade acolhedora</li>
                  <li>🚫 Cancelamento fácil, sem burocracia</li>
                </ul>
                <hr>
                <p class="mb-0" style="color: var(--primary-color); font-weight: 600;">
                  <span style="font-size:1.2em;">⚠️ Vagas limitadas para o trial gratuito!</span><br>
                  <span>Não perca a chance de ser uma mãe ainda mais preparada. <b>Garanta seu acesso agora!</b></span>
                </p>
                <a href="assinatura.php" class="btn btn-primary mt-2" style="font-size:1.1rem; font-weight:600;">
                    🚀 Quero desbloquear meu acesso VIP
                </a>
            </div>
        <?php else: ?>
            <?php if (isInTrial($_SESSION['user_email'], $pdo)): ?>
                <?php $diasRestantes = getTrialDaysLeft($_SESSION['user_email'], $pdo); ?>
                <div class="alert alert-info" role="alert">
                    <h5 class="alert-heading">🎁 Trial Ativo</h5>
                    <p>Você tem <strong><?php echo $diasRestantes; ?> dias</strong> restantes no seu trial gratuito. Aproveite ao máximo!</p>
                    <hr>
                    <p class="mb-0">
                        Após o trial, será cobrado R$ 39/mês automaticamente. <a href="perfil.php" class="alert-link">Gerencie sua assinatura aqui</a>.
                    </p>
                </div>
            <?php else: ?>
                <div class="alert alert-success" role="alert">
                    <h5 class="alert-heading">✅ Assinatura Ativa</h5>
                    <p>Sua assinatura está ativa e você tem acesso completo a todos os materiais!</p>
                    <a href="perfil.php" class="btn btn-outline-success btn-sm">
                        Gerenciar Assinatura
                    </a>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <h2 class="section-title">Feed de Conteúdo Exclusivo</h2>
        <div class="row justify-content-center">
            <?php foreach ($posts as $post): ?>
            <div class="col-12 col-md-6 col-lg-4 mb-5 d-flex align-items-stretch">
                <div class="card h-100" style="border-radius: 24px; overflow: hidden; box-shadow: 0 8px 32px rgba(0,0,0,0.10); padding: 0;">
                    <div style="width: 100%; aspect-ratio: 9/16; background: #000;">
                        <img src="<?php echo htmlspecialchars($post['imagem'], ENT_QUOTES, 'UTF-8'); ?>" alt="Imagem do post" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                    </div>
                    <div class="card-body d-flex flex-column" style="padding: 1.5rem 1rem 1.5rem 1rem;">
                        <h5 class="card-title" style="font-size: 1.3rem; font-weight: 600; color: var(--primary-color); margin-bottom: 0.5rem; text-align: center;">
                            <?php echo htmlspecialchars($post['titulo'], ENT_QUOTES, 'UTF-8'); ?>
                        </h5>
                        <div class="card-subtitle mb-2 text-muted" style="font-size: 1.1rem; text-align: center; font-weight: 500;">
                            <?php echo htmlspecialchars($post['subtitulo'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                        <p class="card-text" style="font-size: 1.1rem; color: var(--text-color); text-align: center; max-height: 4.5em; overflow: hidden; text-overflow: ellipsis; white-space: normal;">
                            <?php echo nl2br(htmlspecialchars($post['texto'], ENT_QUOTES, 'UTF-8')); ?>
                        </p>
                        <?php if (isset($_SESSION['user_email']) && $temAssinatura): ?>
                            <button class="btn btn-primary mt-2 w-100" data-bs-toggle="modal" data-bs-target="#modalPost<?php echo $post['id']; ?>">Ler</button>
                        <?php else: ?>
                            <button class="btn btn-secondary mt-2 w-100" disabled title="Assine para ler o conteúdo completo">Ler</button>
                            <div class="text-center mt-2" style="font-size:0.95em; color:var(--primary-color); font-weight:500;">Assine para desbloquear a leitura</div>
                        <?php endif; ?>
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

    <!-- Modal de Leitura do Post -->
    <?php foreach ($posts as $post): ?>
    <div class="modal fade" id="modalPost<?php echo $post['id']; ?>" tabindex="-1" aria-labelledby="modalPostLabel<?php echo $post['id']; ?>" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header" style="background: var(--primary-color); color: #fff;">
            <div>
              <h5 class="modal-title mb-1" id="modalPostLabel<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['titulo'], ENT_QUOTES, 'UTF-8'); ?></h5>
              <div class="card-subtitle" style="font-size: 1.1rem; color: #ffe66d; font-weight: 500;">
                <?php echo htmlspecialchars($post['subtitulo'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
              </div>
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
          </div>
          <div class="modal-body" style="font-size: 1.35rem; color: var(--text-color); line-height: 1.7; max-height: 70vh; overflow-y: auto;">
            <?php echo nl2br(htmlspecialchars($post['texto'], ENT_QUOTES, 'UTF-8')); ?>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>

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

