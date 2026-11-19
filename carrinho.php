<?php
require_once __DIR__ . '/admin/config/autenticacao.php';
exigirUsuario('login.php?redirect=carrinho.php');
require_once __DIR__ . '/admin/models/social.php';
require_once __DIR__ . '/admin/config/imagens.php';

$usuario = usuarioAtual();
$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!tokenCsrfValido($_POST['csrf_token'] ?? null)) {
        $erro = 'Sessão expirada. Atualize a página e tente novamente.';
    } else {
        try {
            $acao = (string) ($_POST['acao'] ?? '');
            if ($acao === 'adicionar') {
                adicionarAoCarrinho($pdo, $usuario['id'], (int) ($_POST['game_id'] ?? 0), (int) ($_POST['destinatario_id'] ?? 0), (int) ($_POST['quantidade'] ?? 1));
                header('Location: carrinho.php?msg=adicionado'); exit;
            }
            if ($acao === 'remover' || $acao === 'atualizar') {
                $id = (int) ($_POST['item_id'] ?? 0);
                $quantidade = (int) ($_POST['quantidade'] ?? 0);
                if ($id <= 0 || ($acao === 'atualizar' && ($quantidade < 1 || $quantidade > 99))) {
                    throw new RuntimeException('Item ou quantidade inválida.');
                }
                $stmt = $pdo->prepare('SELECT c.id, c.game_id, c.destinatario_id, g.estoque FROM carrinho_itens c JOIN games g ON g.id = c.game_id WHERE c.id = :id AND c.usuario_id = :usuario');
                $stmt->execute([':id' => $id, ':usuario' => $usuario['id']]);
                $item = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$item) { throw new RuntimeException('Item não encontrado.'); }
                if ($acao === 'atualizar' && $quantidade > (int) $item['estoque']) { throw new RuntimeException('Quantidade indisponível em estoque.'); }
                $stmt = $pdo->prepare($acao === 'remover' ? 'DELETE FROM carrinho_itens WHERE id = :id AND usuario_id = :usuario' : 'UPDATE carrinho_itens SET quantidade = :quantidade WHERE id = :id AND usuario_id = :usuario');
                $dados = [':id' => $id, ':usuario' => $usuario['id']];
                if ($acao === 'atualizar') { $dados[':quantidade'] = $quantidade; }
                $stmt->execute($dados);
                header('Location: carrinho.php?msg=atualizado'); exit;
            }
            if ($acao === 'finalizar') {
                $pedidoId = finalizarCarrinho($pdo, $usuario['id']);
                header('Location: presentes.php?pedido=' . $pedidoId); exit;
            }
            throw new RuntimeException('Ação inválida.');
        } catch (RuntimeException $e) {
            $erro = $e->getMessage();
        } catch (PDOException $e) {
            $erro = 'Não foi possível atualizar o carrinho. Confira se o banco foi migrado.';
        }
    }
}
$itens = itensCarrinho($pdo, $usuario['id']);
$total = 0;
foreach ($itens as $item) { $total += (float) $item['preco'] * (int) $item['quantidade']; }
function hCarrinho(string $texto): string { return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Carrinho | Nintendo Store</title><link rel="stylesheet" href="admin/public/css/style.css"></head>
<body class="storefront-page"><a class="skip-link" href="#conteudo">Ir para o conteúdo</a>
<header class="site-header"><nav class="nav" aria-label="Navegação principal"><a class="brand store-brand" href="index.php"><img class="brand-logo" src="admin/public/imagens/logo.png" alt="Nintendo"><span class="brand-store">Store</span></a><div class="nav-links"><a class="nav-link" href="index.php#catalogo">Catálogo</a><a class="nav-link" href="amigos.php">Amigos</a><a class="nav-link" href="presentes.php">Presentes</a><a class="nav-link nav-primary" href="logout.php">Sair</a></div></nav></header>
<main class="container page" id="conteudo"><div class="page-header"><div><p class="eyebrow">Sua seleção</p><h1 class="page-title">Carrinho</h1><p class="page-intro">Revise os jogos e os destinatários antes de confirmar.</p></div><a class="button button-secondary" href="index.php#catalogo">Continuar comprando</a></div>
<?php if ($erro): ?><div class="message message-error" role="alert"><?= hCarrinho($erro) ?></div><?php elseif (isset($_GET['msg'])): ?><div class="message" role="status">Carrinho atualizado.</div><?php endif; ?>
<?php if (!$itens): ?><div class="empty-state"><h2>Seu carrinho está vazio</h2><p>Escolha um jogo para você ou para presentear um amigo.</p><a class="button" href="index.php#catalogo">Ver jogos</a></div><?php else: ?>
<div class="shop-layout"><div class="shop-list">
<?php foreach ($itens as $item): ?><article class="shop-item"><img src="admin/public/imagens/<?= rawurlencode(imagemGameOuPlaceholder($item['imagem'] ?? '')) ?>" alt="" width="160" height="100"><div class="shop-item-body"><h2><a href="jogo.php?id=<?= (int) $item['game_id'] ?>"><?= hCarrinho($item['titulo']) ?></a></h2><p><?= $item['destinatario_id'] ? 'Presente para ' . hCarrinho((string) $item['destinatario_nome']) : 'Para você' ?></p><strong>R$ <?= number_format((float) $item['preco'], 2, ',', '.') ?></strong><div class="shop-actions"><form method="post"><input type="hidden" name="csrf_token" value="<?= hCarrinho(gerarTokenCsrf()) ?>"><input type="hidden" name="acao" value="atualizar"><input type="hidden" name="item_id" value="<?= (int) $item['id'] ?>"><label>Quantidade <input type="number" name="quantidade" min="1" max="99" value="<?= (int) $item['quantidade'] ?>" required></label><button type="submit" class="button-secondary">Atualizar</button></form><form method="post"><input type="hidden" name="csrf_token" value="<?= hCarrinho(gerarTokenCsrf()) ?>"><input type="hidden" name="acao" value="remover"><input type="hidden" name="item_id" value="<?= (int) $item['id'] ?>"><button type="submit" class="button-text">Remover</button></form></div></div></article><?php endforeach; ?>
</div><aside class="shop-summary"><h2>Resumo</h2><p><?= count($itens) ?> <?= count($itens) === 1 ? 'tipo de item' : 'tipos de item' ?></p><div class="shop-total"><span>Total</span><strong>R$ <?= number_format($total, 2, ',', '.') ?></strong></div><form method="post"><input type="hidden" name="csrf_token" value="<?= hCarrinho(gerarTokenCsrf()) ?>"><input type="hidden" name="acao" value="finalizar"><button type="submit">Confirmar pedido</button></form><p class="field-help">Projeto demonstrativo: confirmar reserva os jogos e registra os presentes sem processar pagamento.</p></aside></div>
<?php endif; ?></main></body></html>
