<?php
require_once __DIR__ . '/../config/autenticacao.php';
exigirAdministrador('../../login.php');

require_once __DIR__ . '/../models/games.php';
require_once __DIR__ . '/../config/imagens.php';

$gameModel = new Game();
$games = $gameModel->listarGames();

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

define('MVC_RENDER', true);
require __DIR__ . '/../views/templates/ver_games.php';
