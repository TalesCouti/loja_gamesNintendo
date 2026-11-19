<?php
require_once __DIR__ . '/../config/autenticacao.php';
exigirAdministrador('../../login.php');

require_once __DIR__ . '/../models/usuarios.php';

$usuarioModel = new Usuario();
$mensagem = '';
$nome = '';
$email = '';
$telefone = '';
$tipo = 'user';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim((string) ($_POST['nome'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $telefone = trim((string) ($_POST['telefone'] ?? ''));
    $tipo = (string) ($_POST['tipo'] ?? 'user');

    try {
        if (!tokenCsrfValido($_POST['csrf_token'] ?? null)) {
            throw new RuntimeException('A sessão do formulário expirou. Tente novamente.');
        }

        $usuarioModel->cadastrarUsuario([
            'nome' => $nome,
            'email' => $email,
            'telefone' => $telefone,
            'senha' => $_POST['senha'] ?? '',
            'tipo' => $tipo,
        ]);

        header('Location: ver_usuarios.php?cadastrado=1');
        exit;
    } catch (RuntimeException $e) {
        $mensagem = $e->getMessage();
    }
}

function hUsuario(string $valor): string
{
    return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
}

define('MVC_RENDER', true);
require __DIR__ . '/../views/templates/cadastrar_usuario.php';
