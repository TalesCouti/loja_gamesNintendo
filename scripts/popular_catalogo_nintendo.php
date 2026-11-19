<?php

// Sincroniza o catálogo de uma instalação anterior com os jogos Nintendo do schema.sql.
require_once __DIR__ . '/../admin/config/conexao.php';

$jogos = [
    ['The Legend of Zelda: Tears of the Kingdom', 'Explore as terras e os céus de Hyrule usando novas habilidades para criar seu próprio caminho.', '349.90', 18, 'Aventura', 'zelda-tears-of-the-kingdom.webp'],
    ['Super Mario Bros. Wonder', 'Viaje pelo Reino Flor e descubra fases que se transformam de maneiras surpreendentes.', '299.90', 24, 'Plataforma', 'super-mario-bros-wonder.jpg'],
    ['Mario Kart 8 Deluxe', 'Dispute corridas com Mario e seus amigos em pistas cheias de atalhos, itens e desafios.', '299.90', 30, 'Corrida', 'mario-kart-8-deluxe.webp'],
    ['Animal Crossing: New Horizons', 'Crie sua ilha e viva cada dia no seu próprio ritmo.', '299.90', 16, 'Simulação', 'animal-crossing-new-horizons.webp'],
    ['Pikmin 4', 'Lidere pequenos Pikmin em uma expedição de estratégia e descobertas.', '299.90', 12, 'Estratégia', 'pikmin-4.webp'],
];

$pdo->beginTransaction();
try {
    $categoria = $pdo->prepare('INSERT IGNORE INTO categorias (nome) VALUES (:nome)');
    $plataforma = $pdo->prepare('INSERT IGNORE INTO plataformas (nome) VALUES (:nome)');
    $plataforma->execute([':nome' => 'Nintendo Switch']);

    $buscar = $pdo->prepare('SELECT id FROM games WHERE titulo = :titulo LIMIT 1');
    $inserir = $pdo->prepare(
        'INSERT INTO games (titulo, descricao, preco, estoque, categoria, plataforma, imagem)
         VALUES (:titulo, :descricao, :preco, :estoque, :categoria, :plataforma, :imagem)'
    );
    $atualizarImagem = $pdo->prepare(
        'UPDATE games SET imagem = :imagem, categoria = :categoria, plataforma = :plataforma WHERE id = :id'
    );

    foreach ($jogos as [$titulo, $descricao, $preco, $estoque, $nomeCategoria, $imagem]) {
        if (!is_file(__DIR__ . '/../admin/public/imagens/' . $imagem)) {
            throw new RuntimeException('Imagem ausente: ' . $imagem);
        }
        $categoria->execute([':nome' => $nomeCategoria]);
        $buscar->execute([':titulo' => $titulo]);
        $id = $buscar->fetchColumn();
        if ($id) {
            $atualizarImagem->execute([':imagem' => $imagem, ':categoria' => $nomeCategoria, ':plataforma' => 'Nintendo Switch', ':id' => $id]);
            continue;
        }
        $inserir->execute([
            ':titulo' => $titulo,
            ':descricao' => $descricao,
            ':preco' => $preco,
            ':estoque' => $estoque,
            ':categoria' => $nomeCategoria,
            ':plataforma' => 'Nintendo Switch',
            ':imagem' => $imagem,
        ]);
    }

    // Remove apenas os quatro exemplos antigos sem vínculos com carrinho ou pedidos.
    $removerExemplos = $pdo->prepare(
        'DELETE FROM games WHERE titulo IN ("Bloodborne", "God of War", "The Last of Us", "GTA 6")
         AND NOT EXISTS (SELECT 1 FROM carrinho_itens WHERE carrinho_itens.game_id = games.id)
         AND NOT EXISTS (SELECT 1 FROM pedido_itens WHERE pedido_itens.game_id = games.id)'
    );
    $removerExemplos->execute();

    $pdo->commit();
    echo "Catálogo Nintendo atualizado.\n";
} catch (Throwable $e) {
    $pdo->rollBack();
    throw $e;
}
