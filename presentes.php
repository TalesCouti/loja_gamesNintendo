<?php
require_once __DIR__ . '/admin/config/autenticacao.php';
exigirUsuario('login.php?redirect=presentes.php');
$usuario = usuarioAtual();
$stmt = $pdo->prepare('SELECT pi.titulo, pi.quantidade, pi.preco_unitario, pi.destinatario_id, p.id AS pedido_id, p.created_at, remetente.nome AS remetente_nome, destinatario.nome AS destinatario_nome FROM pedido_itens pi JOIN pedidos p ON p.id = pi.pedido_id JOIN usuarios remetente ON remetente.id = p.usuario_id LEFT JOIN usuarios destinatario ON destinatario.id = pi.destinatario_id WHERE p.usuario_id = :comprador OR pi.destinatario_id = :destinatario ORDER BY p.id DESC, pi.id DESC');
$stmt->execute([':comprador' => $usuario['id'], ':destinatario' => $usuario['id']]);
$historico = $stmt->fetchAll(PDO::FETCH_ASSOC);
$pedidoConfirmado = (int) ($_GET['pedido'] ?? 0);
if ($pedidoConfirmado > 0) {
    $verificar = $pdo->prepare('SELECT 1 FROM pedidos WHERE id = :pedido AND usuario_id = :usuario');
    $verificar->execute([':pedido' => $pedidoConfirmado, ':usuario' => $usuario['id']]);
    if (!$verificar->fetchColumn()) { $pedidoConfirmado = 0; }
}
function hPresente(string $texto): string { return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Pedidos e presentes | Nintendo Store</title><link rel="stylesheet" href="admin/public/css/style.css"></head>
<body class="storefront-page"><a class="skip-link" href="#conteudo">Ir para o conteúdo</a><header class="site-header"><nav class="nav" aria-label="Navegação principal"><a class="brand store-brand" href="index.php"><img class="brand-logo" src="admin/public/imagens/logo.png" alt="Nintendo"><span class="brand-store">Store</span></a><div class="nav-links"><a class="nav-link" href="index.php#catalogo">Catálogo</a><a class="nav-link" href="carrinho.php">Carrinho</a><a class="nav-link" href="amigos.php">Amigos</a><a class="nav-link nav-primary" href="logout.php">Sair</a></div></nav></header>
<main class="container page" id="conteudo"><div class="page-header"><div><p class="eyebrow">Sua conta</p><h1 class="page-title">Pedidos e presentes</h1><p class="page-intro">Acompanhe os jogos reservados para você e os presentes enviados ou recebidos.</p></div></div>
<?php if ($pedidoConfirmado): ?><div class="message" role="status">Pedido #<?= $pedidoConfirmado ?> confirmado. O estoque foi atualizado.</div><?php endif; ?>
<?php if (!$historico): ?><div class="empty-state"><h2>Nenhum pedido ainda</h2><p>Os jogos comprados e os presentes aparecerão aqui.</p><a class="button" href="index.php#catalogo">Explorar jogos</a></div><?php else: ?><div class="shop-list"><?php foreach ($historico as $item): $recebido = (int) $item['destinatario_id'] === $usuario['id']; ?><article class="social-row history-row"><div><span class="eyebrow">Pedido #<?= (int) $item['pedido_id'] ?> · <?= hPresente(date('d/m/Y', strtotime($item['created_at']))) ?></span><h2><?= hPresente($item['titulo']) ?></h2><p><?= $recebido ? 'Presente de ' . hPresente($item['remetente_nome']) : ($item['destinatario_id'] ? 'Presente para ' . hPresente((string) $item['destinatario_nome']) : 'Compra para você') ?></p></div><strong><?= (int) $item['quantidade'] ?> × R$ <?= number_format((float) $item['preco_unitario'], 2, ',', '.') ?></strong></article><?php endforeach; ?></div><?php endif; ?></main></body></html>
