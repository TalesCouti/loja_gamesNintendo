<?php

require_once __DIR__ . '/../config/conexao.php';

abstract class Taxonomia
{
    protected PDO $pdo;
    protected string $tabela;

    public function __construct(PDO $pdo, string $tabela)
    {
        if (!in_array($tabela, ['categorias', 'plataformas'], true)) {
            throw new InvalidArgumentException('Entidade inválida.');
        }
        $this->pdo = $pdo;
        $this->tabela = $tabela;
    }

    public function listar(): array
    {
        return $this->pdo->query("SELECT id, nome, descricao FROM {$this->tabela} ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscar(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT id, nome, descricao FROM {$this->tabela} WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function criar(string $nome, string $descricao): int
    {
        [$nome, $descricao] = $this->validar($nome, $descricao);
        $stmt = $this->pdo->prepare("INSERT INTO {$this->tabela} (nome, descricao) VALUES (:nome, :descricao)");
        $stmt->execute([':nome' => $nome, ':descricao' => $descricao]);
        return (int) $this->pdo->lastInsertId();
    }

    public function atualizar(int $id, string $nome, string $descricao): bool
    {
        if (!$this->buscar($id)) {
            throw new RuntimeException('Registro não encontrado.');
        }
        [$nome, $descricao] = $this->validar($nome, $descricao);
        $stmt = $this->pdo->prepare("UPDATE {$this->tabela} SET nome = :nome, descricao = :descricao WHERE id = :id");
        $stmt->execute([':id' => $id, ':nome' => $nome, ':descricao' => $descricao]);
        return true;
    }

    public function excluir(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->tabela} WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() === 1;
    }

    private function validar(string $nome, string $descricao): array
    {
        $nome = trim($nome);
        $descricao = trim($descricao);
        if ($nome === '' || mb_strlen($nome) > 50 || mb_strlen($descricao) > 255) {
            throw new RuntimeException('Informe nome (até 50 caracteres) e descrição (até 255 caracteres).');
        }
        return [$nome, $descricao];
    }
}
