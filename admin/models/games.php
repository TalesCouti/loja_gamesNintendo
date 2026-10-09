<?php

require_once __DIR__ . '/../config/conexao.php';

class Game
{
    private PDO $pdo;

    private ?int $id = null;
    private string $titulo = '';
    private string $descricao = '';
    private float $preco = 0.0;
    private int $estoque = 0;
    private string $categoria = '';
    private string $imagem = '';

    public function __construct(array $dados = [])
    {
        global $pdo;

        if (!isset($pdo) || !$pdo instanceof PDO) {
            throw new RuntimeException('A conexão PDO não está disponível.');
        }

        $this->pdo = $pdo;
        $this->preencher($dados);
    }

    private function preencher(array $dados): void
    {
        if (isset($dados['id'])) {
            $this->id = (int) $dados['id'];
        }

        if (array_key_exists('titulo', $dados)) {
            $this->titulo = trim((string) $dados['titulo']);
        }

        if (array_key_exists('descricao', $dados)) {
            $this->descricao = trim((string) $dados['descricao']);
        }

        if (array_key_exists('preco', $dados)) {
            $this->preco = (float) str_replace(',', '.', (string) $dados['preco']);
        }

        if (array_key_exists('estoque', $dados)) {
            $this->estoque = (int) $dados['estoque'];
        }

        if (array_key_exists('categoria', $dados)) {
            $this->categoria = trim((string) $dados['categoria']);
        }

        if (array_key_exists('imagem', $dados)) {
            $this->imagem = basename((string) $dados['imagem']);
        }
    }

    private function dadosValidos(): bool
    {
        return $this->titulo !== ''
            && $this->preco >= 0
            && $this->estoque >= 0;
    }

    public function cadastrarGame(): bool
    {
        if (!$this->dadosValidos()) {
            return false;
        }

        $sql = 'INSERT INTO games
                    (titulo, descricao, preco, estoque, categoria, imagem)
                VALUES
                    (:titulo, :descricao, :preco, :estoque, :categoria, :imagem)';

        $stmt = $this->pdo->prepare($sql);
        $resultado = $stmt->execute([
            ':titulo' => $this->titulo,
            ':descricao' => $this->descricao,
            ':preco' => $this->preco,
            ':estoque' => $this->estoque,
            ':categoria' => $this->categoria,
            ':imagem' => $this->imagem,
        ]);

        if ($resultado) {
            $this->id = (int) $this->pdo->lastInsertId();
        }

        return $resultado;
    }

    public function listarGames(): array
    {
        $sql = 'SELECT id, titulo, descricao, preco, estoque, categoria, imagem,
                       created_at, updated_at
                FROM games
                ORDER BY id DESC';

        $stmt = $this->pdo->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarGamePorId($id)
    {
        $id = (int) $id;

        if ($id <= 0) {
            return false;
        }

        $stmt = $this->pdo->prepare(
            'SELECT id, titulo, descricao, preco, estoque, categoria, imagem,
                    created_at, updated_at
             FROM games
             WHERE id = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function comprarGame(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }

        $stmt = $this->pdo->prepare(
            'UPDATE games
             SET estoque = estoque - 1
             WHERE id = :id AND estoque > 0'
        );
        $stmt->execute([':id' => $id]);

        return $stmt->rowCount() === 1;
    }

    public function editarGame(): bool
    {
        if ($this->id === null || $this->id <= 0 || !$this->dadosValidos()) {
            return false;
        }

        $sql = 'UPDATE games
                SET titulo = :titulo,
                    descricao = :descricao,
                    preco = :preco,
                    estoque = :estoque,
                    categoria = :categoria,
                    imagem = :imagem
                WHERE id = :id';

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':id' => $this->id,
            ':titulo' => $this->titulo,
            ':descricao' => $this->descricao,
            ':preco' => $this->preco,
            ':estoque' => $this->estoque,
            ':categoria' => $this->categoria,
            ':imagem' => $this->imagem,
        ]);
    }

    public function deletarGame(): bool
    {
        if ($this->id === null || $this->id <= 0) {
            return false;
        }

        $stmt = $this->pdo->prepare('DELETE FROM games WHERE id = :id');

        return $stmt->execute([':id' => $this->id]);
    }
}
