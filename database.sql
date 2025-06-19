-- Criação do banco de dados
CREATE DATABASE IF NOT EXISTS plataforma_maternidade CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE plataforma_maternidade;

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de PDFs
CREATE TABLE IF NOT EXISTS pdfs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT,
    arquivo VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de vídeos (mantendo estrutura existente)
CREATE TABLE IF NOT EXISTS videos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT,
    url_thumbnail VARCHAR(255),
    url_video VARCHAR(255),
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de assinaturas (atualizada para suportar trials)
CREATE TABLE IF NOT EXISTS assinaturas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_email VARCHAR(255) NOT NULL,
    stripe_customer_id VARCHAR(255),
    stripe_subscription_id VARCHAR(255) UNIQUE,
    status ENUM('trialing', 'active', 'past_due', 'canceled', 'unpaid') DEFAULT 'trialing',
    data_inicio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_fim TIMESTAMP NULL,
    trial_end TIMESTAMP NULL,
    valor DECIMAL(10,2) DEFAULT 39.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_usuario_email (usuario_email),
    INDEX idx_status (status),
    INDEX idx_data_fim (data_fim)
);

-- Tabela de pagamentos recorrentes
CREATE TABLE IF NOT EXISTS pagamentos_recorrentes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stripe_subscription_id VARCHAR(255),
    valor DECIMAL(10,2) NOT NULL,
    status ENUM('succeeded', 'failed', 'pending') DEFAULT 'pending',
    data_pagamento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_subscription_id (stripe_subscription_id),
    INDEX idx_status (status)
);

-- Tabela de posts (imagem + texto, formato vertical tipo Reels)
CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    texto TEXT NOT NULL,
    imagem VARCHAR(255) NOT NULL, -- Caminho do arquivo ou URL
    usuario_email VARCHAR(255), -- Autor do post (opcional)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Inserir usuário de teste
INSERT INTO usuarios (nome, email, senha) VALUES 
('Usuário Teste', 'teste@teste.com', '123456')
ON DUPLICATE KEY UPDATE nome = VALUES(nome);

-- Inserir PDFs de exemplo
INSERT INTO pdfs (titulo, descricao, arquivo) VALUES 
('Guia Completo do Pré-Natal', 'Um guia abrangente sobre todos os aspectos do pré-natal, incluindo consultas, exames e cuidados essenciais.', 'pdfs/guia_pre_natal.pdf'),
('Amamentação: Mitos e Verdades', 'Desmistificando as principais dúvidas sobre amamentação e oferecendo orientações práticas para mães.', 'pdfs/amamentacao_mitos_verdades.pdf')
ON DUPLICATE KEY UPDATE titulo = VALUES(titulo);

-- Inserir vídeos de exemplo (usando estrutura existente)
INSERT INTO videos (titulo, descricao, url_thumbnail, url_video) VALUES 
('Exercícios para Gestantes', 'Série de exercícios seguros e benéficos para gestantes em diferentes trimestres.', 'https://img.youtube.com/vi/exemplo1/0.jpg', 'https://www.youtube.com/watch?v=exemplo1'),
('Preparação para o Parto', 'Orientações completas sobre como se preparar física e emocionalmente para o parto.', 'https://img.youtube.com/vi/exemplo2/0.jpg', 'https://www.youtube.com/watch?v=exemplo2')
ON DUPLICATE KEY UPDATE titulo = VALUES(titulo); 