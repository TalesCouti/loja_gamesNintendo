<?php
require_once __DIR__ . '/admin/config/autenticacao.php';
exigirUsuario('login.php');

require_once __DIR__ . '/admin/models/games.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Método não permitido.');
}

$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0 || !tokenCsrfValido($_POST['csrf_token'] ?? null)) {
    header('Location: index.php?compra=erro');
    exit;
}

$gameModel = new Game();

if ($gameModel->comprarGame($id)) {
    header('Location: jogo.php?id=' . $id . '&compra=sucesso');
    exit;
}

header('Location: jogo.php?id=' . $id . '&erro=estoque');
exit;
