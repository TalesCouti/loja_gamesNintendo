<?php

require_once __DIR__ . '/../config/conexao.php';

function amizadePar(int $a, int $b): array
{
    return [min($a, $b), max($a, $b)];
}

function amigosConfirmados(PDO $pdo, int $usuarioId): array
{
    $stmt = $pdo->prepare('SELECT u.id, u.nome, u.email FROM amizades a JOIN usuarios u ON u.id = IF(a.usuario_a = :id1, a.usuario_b, a.usuario_a) WHERE (a.usuario_a = :id2 OR a.usuario_b = :id3) AND a.status = \'aceita\' ORDER BY u.nome');
    $stmt->execute([':id1' => $usuarioId, ':id2' => $usuarioId, ':id3' => $usuarioId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function saoAmigos(PDO $pdo, int $a, int $b): bool
{
    if ($a <= 0 || $b <= 0 || $a === $b) {
        return false;
    }
    [$menor, $maior] = amizadePar($a, $b);
    $stmt = $pdo->prepare('SELECT 1 FROM amizades WHERE usuario_a = :a AND usuario_b = :b AND status = \'aceita\'');
    $stmt->execute([':a' => $menor, ':b' => $maior]);
    return (bool) $stmt->fetchColumn();
}

function itensCarrinho(PDO $pdo, int $usuarioId): array
{
    $stmt = $pdo->prepare('SELECT c.id, c.game_id, c.destinatario_id, c.quantidade, g.titulo, g.preco, g.estoque, g.imagem, u.nome AS destinatario_nome FROM carrinho_itens c JOIN games g ON g.id = c.game_id LEFT JOIN usuarios u ON u.id = c.destinatario_id WHERE c.usuario_id = :usuario ORDER BY c.id');
    $stmt->execute([':usuario' => $usuarioId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function adicionarAoCarrinho(PDO $pdo, int $usuarioId, int $gameId, int $destinatarioId, int $quantidade): void
{
    if ($gameId <= 0 || $quantidade < 1 || $quantidade > 99 || $destinatarioId < 0 || ($destinatarioId > 0 && !saoAmigos($pdo, $usuarioId, $destinatarioId))) {
        throw new RuntimeException('Jogo, quantidade ou amigo inválido.');
    }
    $stmt = $pdo->prepare('SELECT estoque FROM games WHERE id = :id');
    $stmt->execute([':id' => $gameId]);
    $estoque = $stmt->fetchColumn();
    if ($estoque === false || (int) $estoque < $quantidade) {
        throw new RuntimeException('Quantidade indisponível em estoque.');
    }
    $stmt = $pdo->prepare('SELECT id, quantidade FROM carrinho_itens WHERE usuario_id = :usuario AND game_id = :game AND destinatario_chave = :destinatario');
    $stmt->execute([':usuario' => $usuarioId, ':game' => $gameId, ':destinatario' => $destinatarioId]);
    $atual = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($atual) {
        $novaQuantidade = (int) $atual['quantidade'] + $quantidade;
        if ($novaQuantidade > 99 || $novaQuantidade > (int) $estoque) {
            throw new RuntimeException('Quantidade indisponível em estoque.');
        }
        $update = $pdo->prepare('UPDATE carrinho_itens SET quantidade = :quantidade WHERE id = :id');
        $update->execute([':quantidade' => $novaQuantidade, ':id' => $atual['id']]);
        return;
    }
    $insert = $pdo->prepare('INSERT INTO carrinho_itens (usuario_id, game_id, destinatario_id, destinatario_chave, quantidade) VALUES (:usuario, :game, :destinatario, :chave, :quantidade)');
    $insert->execute([':usuario' => $usuarioId, ':game' => $gameId, ':destinatario' => $destinatarioId ?: null, ':chave' => $destinatarioId, ':quantidade' => $quantidade]);
}

function finalizarCarrinho(PDO $pdo, int $usuarioId): int
{
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('SELECT id, game_id, destinatario_id, quantidade FROM carrinho_itens WHERE usuario_id = :usuario ORDER BY game_id, id FOR UPDATE');
        $stmt->execute([':usuario' => $usuarioId]);
        $itens = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$itens) {
            throw new RuntimeException('O carrinho está vazio.');
        }
        $pedido = $pdo->prepare('INSERT INTO pedidos (usuario_id, total) VALUES (:usuario, 0)');
        $pedido->execute([':usuario' => $usuarioId]);
        $pedidoId = (int) $pdo->lastInsertId();
        $totalCentavos = 0;
        $buscar = $pdo->prepare('SELECT titulo, preco, estoque FROM games WHERE id = :id FOR UPDATE');
        $baixar = $pdo->prepare('UPDATE games SET estoque = estoque - :quantidade WHERE id = :id');
        $registrar = $pdo->prepare('INSERT INTO pedido_itens (pedido_id, game_id, titulo, preco_unitario, quantidade, destinatario_id) VALUES (:pedido, :game, :titulo, :preco, :quantidade, :destinatario)');
        foreach ($itens as $item) {
            $destinatario = (int) ($item['destinatario_id'] ?? 0);
            if ($destinatario && !saoAmigos($pdo, $usuarioId, $destinatario)) {
                throw new RuntimeException('Um destinatário não está mais na sua lista de amigos.');
            }
            $buscar->execute([':id' => $item['game_id']]);
            $game = $buscar->fetch(PDO::FETCH_ASSOC);
            if (!$game || (int) $game['estoque'] < (int) $item['quantidade']) {
                throw new RuntimeException('O estoque mudou. Revise o carrinho.');
            }
            $baixar->execute([':quantidade' => $item['quantidade'], ':id' => $item['game_id']]);
            $registrar->execute([':pedido' => $pedidoId, ':game' => $item['game_id'], ':titulo' => $game['titulo'], ':preco' => $game['preco'], ':quantidade' => $item['quantidade'], ':destinatario' => $destinatario ?: null]);
            $totalCentavos += (int) round((float) $game['preco'] * 100) * (int) $item['quantidade'];
        }
        $update = $pdo->prepare('UPDATE pedidos SET total = :total WHERE id = :id');
        $update->execute([':total' => number_format($totalCentavos / 100, 2, '.', ''), ':id' => $pedidoId]);
        $limpar = $pdo->prepare('DELETE FROM carrinho_itens WHERE usuario_id = :usuario');
        $limpar->execute([':usuario' => $usuarioId]);
        $pdo->commit();
        return $pedidoId;
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}
