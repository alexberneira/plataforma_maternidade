<?php
require_once 'init.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['user_email'])) {
    header('Location: login.php');
    exit;
}

// Restringe acesso ao email autorizado
if ($_SESSION['user_email'] !== 'alexberneira@gmail.com') {
    echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>Acesso Negado</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head><body><div class="container mt-5"><div class="alert alert-danger text-center"><h2>🚫 Acesso negado</h2><p>Esta página é restrita ao administrador.</p><a href="index.php" class="btn btn-primary mt-3">Voltar para o Feed</a></div></div></body></html>';
    exit;
}

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $subtitulo = trim($_POST['subtitulo'] ?? '');
    $texto = trim($_POST['texto'] ?? '');
    $usuario_email = $_SESSION['user_email'];
    $imagem = '';

    // Validação básica
    if (empty($titulo) || empty($subtitulo) || empty($texto)) {
        $erro = 'Preencha todos os campos.';
    } elseif (!isset($_FILES['imagem']) || $_FILES['imagem']['error'] !== UPLOAD_ERR_OK) {
        $erro = 'Envie uma imagem válida.';
    } else {
        // Upload da imagem
        $ext = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
        $permitidas = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($ext, $permitidas)) {
            $erro = 'Formato de imagem não permitido.';
        } else {
            $nomeArquivo = uniqid('post_', true) . '.' . $ext;
            $destino = 'uploads/' . $nomeArquivo;
            if (!is_dir('uploads')) {
                mkdir('uploads', 0777, true);
            }
            if (move_uploaded_file($_FILES['imagem']['tmp_name'], $destino)) {
                $imagem = $destino;
                // Salvar no banco
                $stmt = $pdo->prepare('INSERT INTO posts (titulo, subtitulo, texto, imagem, usuario_email) VALUES (?, ?, ?, ?, ?)');
                if ($stmt->execute([$titulo, $subtitulo, $texto, $imagem, $usuario_email])) {
                    $sucesso = 'Post cadastrado com sucesso!';
                } else {
                    $erro = 'Erro ao salvar no banco.';
                }
            } else {
                $erro = 'Erro ao salvar a imagem.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Novo Post</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { background: #f7f9fc; font-family: 'Poppins', sans-serif; }
        .container { max-width: 500px; margin-top: 40px; }
        .card { border-radius: 18px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .form-label { font-weight: 500; }
        .btn-primary { background: #FF6B6B; border: none; font-weight: 600; }
        .btn-primary:hover { background: #FF5252; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card p-4">
            <h2 class="mb-4 text-center">Novo Post</h2>
            <?php if ($erro): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($erro); ?></div>
            <?php endif; ?>
            <?php if ($sucesso): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($sucesso); ?></div>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Título</label>
                    <input type="text" name="titulo" class="form-control" maxlength="255" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Subtítulo</label>
                    <input type="text" name="subtitulo" class="form-control" maxlength="255" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Texto</label>
                    <textarea name="texto" class="form-control" rows="5" maxlength="2000" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Imagem (jpg, png, webp)</label>
                    <input type="file" name="imagem" class="form-control" accept="image/*" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Cadastrar Post</button>
            </form>
            <a href="index.php" class="btn btn-link mt-3 w-100">Voltar para o Feed</a>
        </div>
    </div>
</body>
</html> 