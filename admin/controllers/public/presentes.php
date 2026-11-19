<?php
require_once __DIR__ . '/../../config/autenticacao.php';
exigirUsuario('login.php?redirect=presentes.php');
$usuario = usuarioAtual();
$stmt = $pdo->prepare('SELECT pi.titulo, pi.quantidade, pi.preco_unitario, pi.destinatario_id, p.id AS pedido_id, p.status, p.created_at, remetente.nome AS remetente_nome, destinatario.nome AS destinatario_nome FROM pedido_itens pi JOIN pedidos p ON p.id = pi.pedido_id JOIN usuarios remetente ON remetente.id = p.usuario_id LEFT JOIN usuarios destinatario ON destinatario.id = pi.destinatario_id WHERE p.usuario_id = :comprador OR pi.destinatario_id = :destinatario ORDER BY p.id DESC, pi.id DESC');
$stmt->execute([':comprador' => $usuario['id'], ':destinatario' => $usuario['id']]);
$historico = $stmt->fetchAll(PDO::FETCH_ASSOC);
$pedidoConfirmado = (int) ($_GET['pedido'] ?? 0);
if ($pedidoConfirmado > 0) {
    $verificar = $pdo->prepare('SELECT 1 FROM pedidos WHERE id = :pedido AND usuario_id = :usuario');
    $verificar->execute([':pedido' => $pedidoConfirmado, ':usuario' => $usuario['id']]);
    if (!$verificar->fetchColumn()) { $pedidoConfirmado = 0; }
}
function hPresente(string $texto): string { return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8'); }

define('MVC_RENDER', true);
require __DIR__ . '/../../views/public/presentes.php';
