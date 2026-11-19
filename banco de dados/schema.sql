CREATE DATABASE IF NOT EXISTS loja_games CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE loja_games;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    telefone VARCHAR(20) NULL,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL UNIQUE,
    descricao VARCHAR(255) NOT NULL DEFAULT ''
);

CREATE TABLE plataformas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL UNIQUE,
    descricao VARCHAR(255) NOT NULL DEFAULT ''
);

CREATE TABLE games (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(100) NOT NULL,
    descricao TEXT NULL,
    preco DECIMAL(10,2) NOT NULL,
    estoque INT NOT NULL DEFAULT 0,
    categoria VARCHAR(50) NULL,
    plataforma VARCHAR(50) NULL,
    imagem VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_games_categoria FOREIGN KEY (categoria) REFERENCES categorias(nome) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_games_plataforma FOREIGN KEY (plataforma) REFERENCES plataformas(nome) ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TABLE amizades (
    usuario_a INT NOT NULL,
    usuario_b INT NOT NULL,
    solicitante_id INT NOT NULL,
    status ENUM('pendente', 'aceita') NOT NULL DEFAULT 'pendente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (usuario_a, usuario_b),
    FOREIGN KEY (usuario_a) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_b) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (solicitante_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE carrinho_itens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    game_id INT NOT NULL,
    destinatario_id INT NULL,
    destinatario_chave INT NOT NULL DEFAULT 0,
    quantidade INT NOT NULL,
    UNIQUE KEY item_unico (usuario_id, game_id, destinatario_chave),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
    FOREIGN KEY (destinatario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    status ENUM('confirmado', 'cancelado') NOT NULL DEFAULT 'confirmado',
    observacao VARCHAR(255) NOT NULL DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE pedido_itens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    game_id INT NULL,
    titulo VARCHAR(100) NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    quantidade INT NOT NULL,
    destinatario_id INT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE SET NULL,
    FOREIGN KEY (destinatario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Hashes gerados em PHP 8 com password_hash(..., PASSWORD_DEFAULT).
INSERT INTO usuarios (nome, email, telefone, senha, tipo) VALUES
('Administrador de teste', 'admin@loja.test', '(11) 99999-0001', '$2y$10$n5DyxbAssn2VNL2/nbVrUe2fSDpR1El0NnYAADx5TWWeMYNmZA3MG', 'admin'),
('Cliente de teste', 'cliente@loja.test', '(11) 99999-0002', '$2y$10$MbtbEW51xv8ZtGD83Ct2LeQVXGZBLzDDWtRX6rhbYFHnwfhqdV5w.', 'user');

INSERT INTO categorias (nome, descricao) VALUES
('Aventura', 'Exploração e narrativa'),
('Plataforma', 'Desafios de percurso'),
('Corrida', 'Jogos de velocidade'),
('Simulação', 'Mundos e rotinas simulados'),
('Estratégia', 'Planejamento e decisões');

INSERT INTO plataformas (nome, descricao) VALUES
('Nintendo Switch', 'Console Nintendo Switch');

INSERT INTO games (titulo, descricao, preco, estoque, categoria, plataforma, imagem) VALUES
('The Legend of Zelda: Tears of the Kingdom', 'Explore as terras e os céus de Hyrule usando novas habilidades para criar seu próprio caminho.', 349.90, 18, 'Aventura', 'Nintendo Switch', 'zelda-tears-of-the-kingdom.webp'),
('Super Mario Bros. Wonder', 'Viaje pelo Reino Flor e descubra fases que se transformam de maneiras surpreendentes.', 299.90, 24, 'Plataforma', 'Nintendo Switch', 'super-mario-bros-wonder.webp'),
('Mario Kart 8 Deluxe', 'Dispute corridas com Mario e seus amigos em pistas cheias de atalhos, itens e desafios.', 299.90, 30, 'Corrida', 'Nintendo Switch', 'mario-kart-8-deluxe.webp'),
('Animal Crossing: New Horizons', 'Crie sua ilha e viva cada dia no seu próprio ritmo.', 299.90, 16, 'Simulação', 'Nintendo Switch', 'animal-crossing-new-horizons.webp'),
('Pikmin 4', 'Lidere pequenos Pikmin em uma expedição de estratégia e descobertas.', 299.90, 12, 'Estratégia', 'Nintendo Switch', 'pikmin-4.webp');
