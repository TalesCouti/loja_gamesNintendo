<?php
require_once __DIR__ . '/../../config/autenticacao.php';
require_once __DIR__ . '/../../models/games.php';
require_once __DIR__ . '/../../config/imagens.php';
require_once __DIR__ . '/../../models/social.php';

iniciarSessaoSegura();
if (usuarioAutenticado() && !sincronizarSessaoUsuario($pdo)) {
    encerrarSessao();
    iniciarSessaoSegura();
}

$gameModel = new Game();
$game = $gameModel->buscarGamePorId((int) ($_GET['id'] ?? 0));
$usuario = usuarioAtual();
$amigos = $usuario ? amigosConfirmados($pdo, $usuario['id']) : [];
$imagemJogo = $game
    ? imagemGameOuPlaceholder($game['imagem'] ?? '')
    : GAME_PLACEHOLDER_IMAGE;

if (!$game) {
    http_response_code(404);
}

function hJogo(string $valor): string
{
    return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
}

function primeiroNome(?array $usuario): string
{
    if (!$usuario) {
        return '';
    }

    return explode(' ', trim((string) $usuario['nome']))[0] ?? '';
}

define('MVC_RENDER', true);
require __DIR__ . '/../../views/public/jogo.php';
