<?php
require_once __DIR__ . '/admin/config/autenticacao.php';
exigirUsuario('login.php');

require_once __DIR__ . '/admin/models/social.php';

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

try {
    adicionarAoCarrinho($pdo, usuarioAtual()['id'], $id, 0, 1);
    header('Location: carrinho.php?msg=adicionado');
} catch (RuntimeException $e) {
    header('Location: jogo.php?id=' . $id . '&erro=estoque');
}
exit;
