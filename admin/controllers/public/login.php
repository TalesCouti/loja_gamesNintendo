<?php
require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../../config/autenticacao.php';

iniciarSessaoSegura();

function destinoSeguroAposLogin(?string $destino): string
{
    if (!is_string($destino) || $destino === '') {
        return 'index.php';
    }

    $partes = parse_url($destino);
    if ($partes === false || isset($partes['scheme']) || isset($partes['host'])) {
        return 'index.php';
    }

    $caminho = ltrim((string) ($partes['path'] ?? ''), '/');
    if ($caminho === 'jogo.php') {
        parse_str((string) ($partes['query'] ?? ''), $consulta);
        $id = (int) ($consulta['id'] ?? 0);

        return $id > 0 ? 'jogo.php?id=' . $id : 'index.php';
    }

    return in_array($caminho, ['index.php', 'carrinho.php', 'amigos.php', 'presentes.php'], true) ? $caminho : 'index.php';
}

$destinoSolicitado = (string) ($_POST['redirect'] ?? $_GET['redirect'] ?? '');

if (usuarioAutenticado()) {
    header('Location: ' . (
        usuarioAdministradorAutenticado() && $destinoSolicitado === ''
            ? 'admin/views/ver_games.php'
            : destinoSeguroAposLogin($destinoSolicitado)
    ));
    exit;
}

$mensagem = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $senhaInformada = (string) ($_POST['senha'] ?? '');
    $token = $_POST['csrf_token'] ?? null;

    if (
        tokenCsrfValido(is_string($token) ? $token : null)
        && filter_var($email, FILTER_VALIDATE_EMAIL)
        && $senhaInformada !== ''
        && autenticarUsuario($pdo, $email, $senhaInformada)
    ) {
        header('Location: ' . (
            usuarioAdministradorAutenticado() && $destinoSolicitado === ''
                ? 'admin/views/ver_games.php'
                : destinoSeguroAposLogin($destinoSolicitado)
        ));
        exit;
    }

    $mensagem = 'E-mail ou senha inválidos.';
}

define('MVC_RENDER', true);
require __DIR__ . '/../../views/public/login.php';
