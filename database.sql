-- Criação do banco de dados
CREATE DATABASE IF NOT EXISTS plataforma_maternidade
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE plataforma_maternidade;

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de vídeos
CREATE TABLE IF NOT EXISTS videos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT,
    url_thumbnail VARCHAR(255),
    url_video VARCHAR(255),
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de PDFs
CREATE TABLE IF NOT EXISTS pdfs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT,
    arquivo VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir usuários de teste
INSERT INTO usuarios (nome, email, senha, ativo) VALUES 
('Usuário Teste', 'teste@exemplo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE),
('Alex Berneira', 'alexberneira@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE);

-- Inserir PDFs de exemplo
INSERT INTO pdfs (titulo, descricao, arquivo) VALUES 
('Guia de Pré-Natal', 'Orientações completas sobre o pré-natal', 'pre-natal.pdf'),
('Amamentação', 'Guia completo sobre amamentação', 'amamentacao.pdf'),
('Nutrição na Gestação', 'Dicas de alimentação durante a gravidez', 'nutricao.pdf'),
('Exercícios para Gestantes', 'Exercícios seguros durante a gravidez', 'exercicios.pdf'),
('Preparação para o Parto', 'Orientações para o momento do parto', 'parto.pdf');

-- Inserir alguns dados de exemplo
INSERT INTO videos (titulo, descricao, url_thumbnail, url_video) VALUES
('O início da maternidade', 'Vídeo sobre os primeiros passos da maternidade', 'https://img.youtube.com/vi/1Q8fG0TtVAY/0.jpg', 'https://youtube.com/watch?v=1Q8fG0TtVAY'),
('Dicas para o dia a dia', 'Dicas práticas para o dia a dia', 'https://img.youtube.com/vi/2Vv-BfVoq4g/0.jpg', 'https://youtube.com/watch?v=2Vv-BfVoq4g'),
('Amamentação sem tabu', 'Tudo sobre amamentação', 'https://img.youtube.com/vi/3JZ_D3ELwOQ/0.jpg', 'https://youtube.com/watch?v=3JZ_D3ELwOQ'); 