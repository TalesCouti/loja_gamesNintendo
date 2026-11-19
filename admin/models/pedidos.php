<?php

require_once __DIR__ . '/../config/conexao.php';

class Pedido
{
    public function __construct(private PDO $pdo) {}

    public function listar(): array
    {
        return $this->pdo->query(
            'SELECT p.id, p.usuario_id, u.nome AS cliente, p.total, p.status, p.observacao, p.created_at,
                    GROUP_CONCAT(CONCAT(pi.quantidade, "× ", pi.titulo) ORDER BY pi.id SEPARATOR ", ") AS itens
             FROM pedidos p JOIN usuarios u ON u.id = p.usuario_id
             LEFT JOIN pedido_itens pi ON pi.pedido_id = p.id
             GROUP BY p.id, p.usuario_id, u.nome, p.total, p.status, p.observacao, p.created_at
             ORDER BY p.id DESC'
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscar(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, usuario_id, total, status, observacao, created_at FROM pedidos WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function criar(int $usuarioId, int $gameId, int $quantidade, string $observacao = ''): int
    {
        $observacao = $this->validarObservacao($observacao);
        if ($usuarioId <= 0 || $gameId <= 0 || $quantidade < 1 || $quantidade > 99) {
            throw new RuntimeException('Cliente, jogo ou quantidade inválidos.');
        }
        $this->pdo->beginTransaction();
        try {
            $usuario = $this->pdo->prepare('SELECT id FROM usuarios WHERE id = :id');
            $usuario->execute([':id' => $usuarioId]);
            if (!$usuario->fetchColumn()) { throw new RuntimeException('Cliente não encontrado.'); }
            $stmt = $this->pdo->prepare('SELECT id, titulo, preco, estoque FROM games WHERE id = :id FOR UPDATE');
            $stmt->execute([':id' => $gameId]);
            $game = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$game || (int) $game['estoque'] < $quantidade) {
                throw new RuntimeException('Estoque insuficiente para criar o pedido.');
            }
            $total = number_format((int) round((float) $game['preco'] * 100) * $quantidade / 100, 2, '.', '');
            $stmt = $this->pdo->prepare('INSERT INTO pedidos (usuario_id, total, observacao) VALUES (:usuario, :total, :observacao)');
            $stmt->execute([':usuario' => $usuarioId, ':total' => $total, ':observacao' => $observacao]);
            $pedidoId = (int) $this->pdo->lastInsertId();
            $stmt = $this->pdo->prepare('INSERT INTO pedido_itens (pedido_id, game_id, titulo, preco_unitario, quantidade) VALUES (:pedido, :game, :titulo, :preco, :quantidade)');
            $stmt->execute([':pedido' => $pedidoId, ':game' => $gameId, ':titulo' => $game['titulo'], ':preco' => $game['preco'], ':quantidade' => $quantidade]);
            $stmt = $this->pdo->prepare('UPDATE games SET estoque = estoque - :quantidade WHERE id = :id');
            $stmt->execute([':quantidade' => $quantidade, ':id' => $gameId]);
            $this->pdo->commit();
            return $pedidoId;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function atualizar(int $id, string $status, string $observacao): void
    {
        if (!in_array($status, ['confirmado', 'cancelado'], true)) {
            throw new RuntimeException('Status inválido.');
        }
        $observacao = $this->validarObservacao($observacao);
        $this->pdo->beginTransaction();
        try {
            $pedido = $this->bloquear($id);
            if ($pedido['status'] !== $status) {
                $this->ajustarEstoque($id, $status === 'cancelado' ? 1 : -1);
            }
            $stmt = $this->pdo->prepare('UPDATE pedidos SET status = :status, observacao = :observacao WHERE id = :id');
            $stmt->execute([':id' => $id, ':status' => $status, ':observacao' => $observacao]);
            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function excluir(int $id): void
    {
        $this->pdo->beginTransaction();
        try {
            $pedido = $this->bloquear($id);
            if ($pedido['status'] === 'confirmado') {
                $this->ajustarEstoque($id, 1);
            }
            $stmt = $this->pdo->prepare('DELETE FROM pedidos WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function bloquear(int $id): array
    {
        $stmt = $this->pdo->prepare('SELECT id, status FROM pedidos WHERE id = :id FOR UPDATE');
        $stmt->execute([':id' => $id]);
        $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$pedido) { throw new RuntimeException('Pedido não encontrado.'); }
        return $pedido;
    }

    private function ajustarEstoque(int $pedidoId, int $direcao): void
    {
        $stmt = $this->pdo->prepare('SELECT game_id, quantidade FROM pedido_itens WHERE pedido_id = :id AND game_id IS NOT NULL ORDER BY game_id');
        $stmt->execute([':id' => $pedidoId]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $item) {
            $game = $this->pdo->prepare('SELECT estoque FROM games WHERE id = :id FOR UPDATE');
            $game->execute([':id' => $item['game_id']]);
            $estoque = $game->fetchColumn();
            if ($estoque === false) { continue; }
            if ($direcao < 0 && (int) $estoque < (int) $item['quantidade']) {
                throw new RuntimeException('Estoque insuficiente para reativar o pedido.');
            }
            $atualizar = $this->pdo->prepare('UPDATE games SET estoque = estoque + :delta WHERE id = :id');
            $atualizar->execute([':delta' => $direcao * (int) $item['quantidade'], ':id' => $item['game_id']]);
        }
    }

    private function validarObservacao(string $observacao): string
    {
        $observacao = trim($observacao);
        if (mb_strlen($observacao) > 255) {
            throw new RuntimeException('A observação deve ter até 255 caracteres.');
        }
        return $observacao;
    }
}
