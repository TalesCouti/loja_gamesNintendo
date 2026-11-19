<?php
require_once __DIR__ . '/../config/autenticacao.php';
exigirAdministrador('../../login.php');

require_once __DIR__ . '/../models/usuarios.php';

$usuarioModel = new Usuario();
$id = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);
$dados = $usuarioModel->buscarUsuarioPorId($id);
$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!tokenCsrfValido($_POST['csrf_token'] ?? null)) {
            throw new RuntimeException('A sessão do formulário expirou. Tente novamente.');
        }

        $tipo = (string) ($_POST['tipo'] ?? 'user');
        if ($id === (int) $_SESSION['usuario_id'] && $tipo !== 'admin') {
            throw new RuntimeException('Você não pode remover o próprio acesso administrativo.');
        }

        $usuarioModel->editarUsuario($id, [
            'nome' => $_POST['nome'] ?? '',
            'email' => $_POST['email'] ?? '',
            'telefone' => $_POST['telefone'] ?? '',
            'senha' => $_POST['senha'] ?? '',
            'tipo' => $tipo,
        ]);

        if ($id === (int) $_SESSION['usuario_id']) {
            $_SESSION['usuario_nome'] = trim((string) $_POST['nome']);
            $_SESSION['usuario_email'] = strtolower(trim((string) $_POST['email']));
        }

        header('Location: ver_usuarios.php?editado=1');
        exit;
    } catch (RuntimeException $e) {
        $mensagem = $e->getMessage();
        $dados = array_merge($dados ?: [], [
            'id' => $id,
            'nome' => trim((string) ($_POST['nome'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'telefone' => trim((string) ($_POST['telefone'] ?? '')),
            'tipo' => (string) ($_POST['tipo'] ?? 'user'),
        ]);
    }
}

function hUsuario(string $valor): string
{
    return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
}

define('MVC_RENDER', true);
require __DIR__ . '/../views/templates/editar_usuario.php';
