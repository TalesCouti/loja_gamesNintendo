<?php
require_once __DIR__ . '/../../models/games.php';
require_once __DIR__ . '/../../config/imagens.php';
require_once __DIR__ . '/../../config/autenticacao.php';

iniciarSessaoSegura();
if (usuarioAutenticado() && !sincronizarSessaoUsuario($pdo)) {
    encerrarSessao();
    iniciarSessaoSegura();
}
$gameModel = new Game();
$games = $gameModel->listarGames();
$totalGames = count($games);
$usuario = usuarioAtual();

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function primeiroNomeLoja(?array $usuario): string
{
    if (!$usuario) {
        return '';
    }

    return explode(' ', trim((string) $usuario['nome']))[0] ?? '';
}

define('MVC_RENDER', true);
require __DIR__ . '/../../views/public/index.php';
