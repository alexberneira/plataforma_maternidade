-- Recria a tabela pdfs com encoding correto
DROP TABLE IF EXISTS pdfs;
CREATE TABLE pdfs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT,
    arquivo VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Limpa a tabela pdfs
TRUNCATE TABLE pdfs;

-- Insere os dados com encoding correto
INSERT INTO pdfs (titulo, descricao, arquivo) VALUES 
('Guia Completo do Pré-Natal', 'Um guia completo e detalhado que vai te acompanhar durante toda a gestação. Aprenda sobre as consultas essenciais, exames importantes, cuidados com a saúde, alimentação adequada, exercícios recomendados e muito mais. Tudo o que você precisa saber para ter um pré-natal tranquilo e saudável.', 'pre-natal.pdf'),
('Amamentação: Mitos e Verdades', 'Um guia completo que desmistifica a amamentação e te prepara para esse momento especial. Aprenda sobre posições corretas, técnicas de pega, cuidados com as mamas, alimentação da mãe, soluções para problemas comuns e dicas práticas para uma amamentação bem-sucedida e prazerosa.', 'amamentacao.pdf'),
('Nutrição na Gestação', 'Um guia nutricional completo para cada fase da sua gestação. Descubra quais alimentos são essenciais para o desenvolvimento do seu bebê, como manter uma alimentação equilibrada, quais suplementos são necessários, como controlar o ganho de peso de forma saudável e receitas nutritivas para cada trimestre.', 'nutricao.pdf'),
('Exercícios para Gestantes', 'Um guia prático de exercícios seguros e benéficos para cada trimestre da gestação. Aprenda exercícios de fortalecimento, alongamento, respiração e relaxamento. Inclui dicas de postura, cuidados especiais, contraindicações e como adaptar sua rotina de exercícios durante a gravidez.', 'exercicios.pdf'),
('Preparação para o Parto', 'Um guia completo que te prepara física e emocionalmente para o grande momento. Aprenda sobre os diferentes tipos de parto, sinais de trabalho de parto, técnicas de respiração, posições para alívio da dor, cuidados pós-parto, recuperação e os primeiros cuidados com o bebê.', 'parto.pdf'); 