<?php
require_once __DIR__ . '/../config/autenticacao.php';
exigirAdministrador('../../login.php');

require_once __DIR__ . '/../models/games.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Método não permitido.');
}

if (!tokenCsrfValido($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('A sessão da ação expirou.');
}

$game = new Game(['id' => $_POST['id'] ?? 0]);
if (!$game->deletarGame()) {
    http_response_code(400);
    exit('Não foi possível excluir o jogo.');
}

header('Location: ../views/ver_games.php?deletado=1');
exit;
