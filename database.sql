-- Configuração do banco de dados
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Criar banco de dados se não existir
CREATE DATABASE IF NOT EXISTS `plataforma_maternidade` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `plataforma_maternidade`;

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de assinaturas
CREATE TABLE IF NOT EXISTS `assinaturas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `stripe_subscription_id` varchar(255) NOT NULL,
  `stripe_customer_id` varchar(255) NOT NULL,
  `status` enum('active','canceled','past_due','unpaid') NOT NULL DEFAULT 'active',
  `trial_start` timestamp NULL DEFAULT NULL,
  `trial_end` timestamp NULL DEFAULT NULL,
  `current_period_start` timestamp NULL DEFAULT NULL,
  `current_period_end` timestamp NULL DEFAULT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `stripe_subscription_id` (`stripe_subscription_id`),
  CONSTRAINT `assinaturas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de pagamentos recorrentes
CREATE TABLE IF NOT EXISTS `pagamentos_recorrentes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `assinatura_id` int(11) NOT NULL,
  `stripe_payment_intent_id` varchar(255) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `status` enum('succeeded','failed','pending') NOT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `assinatura_id` (`assinatura_id`),
  CONSTRAINT `pagamentos_recorrentes_ibfk_1` FOREIGN KEY (`assinatura_id`) REFERENCES `assinaturas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de posts
CREATE TABLE IF NOT EXISTS `posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) NOT NULL,
  `subtitulo` varchar(255) DEFAULT NULL,
  `texto` text NOT NULL,
  `imagem` varchar(255) DEFAULT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir posts de exemplo
INSERT INTO posts (titulo, subtitulo, texto, imagem) VALUES
('Amamentação: Guia Completo', 'Tudo que você precisa saber sobre amamentação', 'A amamentação é uma das experiências mais importantes e desafiadoras da maternidade. Neste guia completo, vamos abordar todos os aspectos essenciais para uma amamentação bem-sucedida.

**Por que a amamentação é importante?**

A amamentação oferece inúmeros benefícios tanto para o bebê quanto para a mãe:

* **Para o bebê:**
  - Fornece todos os nutrientes necessários
  - Fortalece o sistema imunológico
  - Reduz o risco de infecções
  - Promove o desenvolvimento cerebral
  - Cria um vínculo emocional único

* **Para a mãe:**
  - Ajuda na recuperação pós-parto
  - Reduz o risco de câncer de mama e ovário
  - Economiza tempo e dinheiro
  - Promove a perda de peso natural

**Posicionamento correto**

O posicionamento correto é fundamental para o sucesso da amamentação:

1. **Posição da mãe:** Sente-se confortavelmente com as costas apoiadas
2. **Posição do bebê:** Barriga com barriga, nariz alinhado com o mamilo
3. **Boca do bebê:** Deve abrir bem a boca e pegar toda a aréola
4. **Queixo:** Deve estar tocando o seio

**Sinais de uma boa pega:**

- Boca bem aberta
- Lábio inferior virado para fora
- Queixo tocando o seio
- Bochechas arredondadas
- Movimento de sucção rítmico
- Sem dor para a mãe

**Frequência da amamentação:**

- **Recém-nascidos:** 8-12 vezes por dia
- **1-2 meses:** 7-9 vezes por dia
- **3-6 meses:** 6-8 vezes por dia

**Como saber se o bebê está mamando bem:**

- Faz movimentos de sucção regulares
- Você pode ouvir o som da deglutição
- O bebê fica relaxado após a mamada
- As fraldas ficam molhadas regularmente
- O bebê ganha peso adequadamente

**Problemas comuns e soluções:**

**Rachaduras nos mamilos:**
- Verifique o posicionamento
- Use pomadas específicas
- Exponha os mamilos ao ar
- Comece a mamada pelo seio menos dolorido

**Ingurgitamento:**
- Amamente com frequência
- Use compressas quentes antes da mamada
- Massageie suavemente o seio
- Use sutiã de amamentação adequado

**Mastite:**
- Continue amamentando
- Descanse bastante
- Use compressas quentes
- Procure ajuda médica se necessário

**Dicas importantes:**

1. **Confie no seu corpo:** Seu corpo foi feito para isso
2. **Busque apoio:** Não hesite em pedir ajuda
3. **Seja paciente:** Pode levar tempo para se estabelecer
4. **Cuide-se:** Descanse e alimente-se bem
5. **Não desista:** Os primeiros dias são os mais difíceis

**Quando procurar ajuda:**

- Dor intensa durante a amamentação
- Rachaduras que não melhoram
- Febre ou sintomas de mastite
- Bebê não está ganhando peso
- Dúvidas sobre a técnica

Lembre-se: cada mãe e bebê são únicos. O que funciona para uma pode não funcionar para outra. O importante é encontrar o que funciona melhor para vocês dois.', 'uploads/post_685464e0130ec8.96565586.webp');

COMMIT; 