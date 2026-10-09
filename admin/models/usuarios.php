<?php

require_once __DIR__ . '/../config/conexao.php';

class Usuario
{
    private PDO $pdo;

    public function __construct()
    {
        global $pdo;

        if (!isset($pdo) || !$pdo instanceof PDO) {
            throw new RuntimeException('A conexão PDO não está disponível.');
        }

        $this->pdo = $pdo;
    }

    public function listarUsuarios(): array
    {
        $stmt = $this->pdo->query(
            'SELECT id, nome, email, tipo, created_at, updated_at
             FROM usuarios
             ORDER BY nome, id'
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarUsuarioPorId(int $id)
    {
        if ($id <= 0) {
            return false;
        }

        $stmt = $this->pdo->prepare(
            'SELECT id, nome, email, tipo, created_at, updated_at
             FROM usuarios
             WHERE id = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function cadastrarUsuario(array $dados): bool
    {
        $nome = trim((string) ($dados['nome'] ?? ''));
        $email = strtolower(trim((string) ($dados['email'] ?? '')));
        $senha = (string) ($dados['senha'] ?? '');
        $tipo = $this->normalizarTipo((string) ($dados['tipo'] ?? 'user'));

        $this->validarDados($nome, $email, $senha, true);
        $this->garantirEmailDisponivel($email);

        $stmt = $this->pdo->prepare(
            'INSERT INTO usuarios (nome, email, senha, tipo)
             VALUES (:nome, :email, :senha, :tipo)'
        );

        try {
            return $stmt->execute([
                ':nome' => $nome,
                ':email' => $email,
                ':senha' => password_hash($senha, PASSWORD_DEFAULT),
                ':tipo' => $tipo,
            ]);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                throw new RuntimeException('Este e-mail já está cadastrado.');
            }

            throw new RuntimeException('Não foi possível cadastrar o usuário.');
        }
    }

    public function editarUsuario(int $id, array $dados): bool
    {
        $usuarioAtual = $this->buscarUsuarioPorId($id);
        if (!$usuarioAtual) {
            throw new RuntimeException('Usuário não encontrado.');
        }

        $nome = trim((string) ($dados['nome'] ?? ''));
        $email = strtolower(trim((string) ($dados['email'] ?? '')));
        $senha = (string) ($dados['senha'] ?? '');
        $tipo = $this->normalizarTipo((string) ($dados['tipo'] ?? 'user'));

        $this->validarDados($nome, $email, $senha, false);
        $this->garantirEmailDisponivel($email, $id);

        if ($usuarioAtual['tipo'] === 'admin' && $tipo !== 'admin') {
            $this->garantirOutroAdministrador($id);
        }

        $sql = 'UPDATE usuarios
                SET nome = :nome, email = :email, tipo = :tipo';
        $parametros = [
            ':id' => $id,
            ':nome' => $nome,
            ':email' => $email,
            ':tipo' => $tipo,
        ];

        if ($senha !== '') {
            $sql .= ', senha = :senha';
            $parametros[':senha'] = password_hash($senha, PASSWORD_DEFAULT);
        }

        $sql .= ' WHERE id = :id';

        $stmt = $this->pdo->prepare($sql);

        try {
            return $stmt->execute($parametros);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                throw new RuntimeException('Este e-mail já está cadastrado.');
            }

            throw new RuntimeException('Não foi possível atualizar o usuário.');
        }
    }

    public function deletarUsuario(int $id, int $usuarioLogadoId): bool
    {
        if ($id <= 0) {
            throw new RuntimeException('Usuário inválido.');
        }

        if ($id === $usuarioLogadoId) {
            throw new RuntimeException('Você não pode excluir a própria conta enquanto está conectado.');
        }

        $usuario = $this->buscarUsuarioPorId($id);
        if (!$usuario) {
            throw new RuntimeException('Usuário não encontrado.');
        }

        if ($usuario['tipo'] === 'admin') {
            $this->garantirOutroAdministrador($id);
        }

        $stmt = $this->pdo->prepare('DELETE FROM usuarios WHERE id = :id');

        return $stmt->execute([':id' => $id]);
    }

    private function validarDados(string $nome, string $email, string $senha, bool $senhaObrigatoria): void
    {
        if ($nome === '' || mb_strlen($nome) > 100) {
            throw new RuntimeException('Informe um nome com até 100 caracteres.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
            throw new RuntimeException('Informe um e-mail válido.');
        }

        if (($senhaObrigatoria || $senha !== '') && strlen($senha) < 8) {
            throw new RuntimeException('A senha deve ter pelo menos 8 caracteres.');
        }
    }

    private function normalizarTipo(string $tipo): string
    {
        if (!in_array($tipo, ['admin', 'user'], true)) {
            throw new RuntimeException('Selecione um tipo de usuário válido.');
        }

        return $tipo;
    }

    private function garantirEmailDisponivel(string $email, ?int $ignorarId = null): void
    {
        $sql = 'SELECT id FROM usuarios WHERE email = :email';
        $parametros = [':email' => $email];

        if ($ignorarId !== null) {
            $sql .= ' AND id <> :id';
            $parametros[':id'] = $ignorarId;
        }

        $sql .= ' LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($parametros);

        if ($stmt->fetchColumn()) {
            throw new RuntimeException('Este e-mail já está cadastrado.');
        }
    }

    private function garantirOutroAdministrador(int $idIgnorado): void
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*)
             FROM usuarios
             WHERE tipo = :tipo AND id <> :id'
        );
        $stmt->execute([
            ':tipo' => 'admin',
            ':id' => $idIgnorado,
        ]);

        if ((int) $stmt->fetchColumn() < 1) {
            throw new RuntimeException('A loja precisa manter pelo menos um administrador.');
        }
    }
}
