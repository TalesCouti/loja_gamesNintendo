-- Execute uma vez em instalações que já possuem usuarios e games.
CREATE TABLE IF NOT EXISTS amizades (
    usuario_a INT NOT NULL, usuario_b INT NOT NULL, solicitante_id INT NOT NULL,
    status ENUM('pendente', 'aceita') NOT NULL DEFAULT 'pendente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (usuario_a, usuario_b),
    FOREIGN KEY (usuario_a) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_b) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (solicitante_id) REFERENCES usuarios(id) ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS carrinho_itens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL, game_id INT NOT NULL,
    destinatario_id INT NULL, destinatario_chave INT NOT NULL DEFAULT 0,
    quantidade INT NOT NULL,
    UNIQUE KEY item_unico (usuario_id, game_id, destinatario_chave),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
    FOREIGN KEY (destinatario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY, usuario_id INT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS pedido_itens (
    id INT AUTO_INCREMENT PRIMARY KEY, pedido_id INT NOT NULL,
    game_id INT NULL, titulo VARCHAR(100) NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL, quantidade INT NOT NULL,
    destinatario_id INT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE SET NULL,
    FOREIGN KEY (destinatario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);
