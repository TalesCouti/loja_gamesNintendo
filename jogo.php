<?php
require_once __DIR__ . '/admin/config/autenticacao.php';
require_once __DIR__ . '/admin/models/games.php';
require_once __DIR__ . '/admin/config/imagens.php';

iniciarSessaoSegura();
if (usuarioAutenticado() && !sincronizarSessaoUsuario($pdo)) {
    encerrarSessao();
    iniciarSessaoSegura();
}

$gameModel = new Game();
$game = $gameModel->buscarGamePorId((int) ($_GET['id'] ?? 0));
$usuario = usuarioAtual();
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
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#e60012">
    <meta name="description" content="<?= $game ? hJogo((string) $game['descricao']) : 'Jogo não encontrado na Nintendo Store.' ?>">
    <title><?= $game ? hJogo((string) $game['titulo']) . ' | Nintendo Store' : 'Jogo não encontrado | Nintendo Store' ?></title>
    <link rel="stylesheet" href="admin/public/css/style.css">
</head>
<body class="storefront-page">
    <a class="skip-link" href="#conteudo">Ir para o conteúdo</a>
    <header class="site-header">
        <nav class="nav" aria-label="Navegação principal">
            <a class="brand store-brand" href="index.php" aria-label="Nintendo Store - página inicial">
                <img class="brand-logo" src="admin/public/imagens/logo.png" alt="" aria-hidden="true">
                <span class="brand-store">Store</span>
            </a>
            <div class="nav-links">
                <a class="nav-link" href="index.php#catalogo">Catálogo</a>
                <?php if ($usuario): ?>
                    <span class="nav-user">Olá, <?= hJogo(primeiroNome($usuario)) ?></span>
                    <?php if ($usuario['tipo'] === 'admin'): ?>
                        <a class="nav-link" href="admin/views/ver_games.php">Painel</a>
                    <?php endif; ?>
                    <a class="nav-link nav-primary" href="logout.php">Sair</a>
                <?php else: ?>
                    <a class="nav-link nav-primary" href="login.php?redirect=<?= rawurlencode('jogo.php?id=' . (int) ($game['id'] ?? 0)) ?>">Entrar</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <main class="container game-detail-page" id="conteudo">
        <a class="back-link" href="index.php#catalogo">Voltar ao catálogo</a>

        <?php if ($game): ?>
            <?php if (isset($_GET['compra']) && $_GET['compra'] === 'sucesso'): ?>
                <div class="message purchase-message" role="status">Compra realizada. O estoque foi atualizado.</div>
            <?php elseif (isset($_GET['erro'])): ?>
                <div class="message message-error purchase-message" role="alert">Não foi possível concluir a compra. Verifique o estoque e tente novamente.</div>
            <?php endif; ?>

            <article class="game-detail">
                <div class="game-detail-media" style="--game-art: url('admin/public/imagens/<?= rawurlencode($imagemJogo) ?>')">
                    <img src="admin/public/imagens/<?= rawurlencode($imagemJogo) ?>" width="1200" height="675" alt="Imagem de <?= hJogo((string) $game['titulo']) ?>">
                </div>
                <div class="game-detail-content">
                    <div>
                        <span class="game-platform">Nintendo Switch</span>
                        <h1><?= hJogo((string) $game['titulo']) ?></h1>
                        <p class="game-detail-description"><?= hJogo((string) ($game['descricao'] ?: 'Uma nova experiência espera por você.')) ?></p>
                        <div class="game-detail-meta">
                            <span><?= hJogo((string) ($game['categoria'] ?: 'Sem categoria')) ?></span>
                            <span class="<?= (int) $game['estoque'] > 0 ? 'in-stock' : 'out-of-stock' ?>">
                                <?= (int) $game['estoque'] > 0 ? (int) $game['estoque'] . ' unidades disponíveis' : 'Produto esgotado' ?>
                            </span>
                        </div>
                    </div>

                    <div class="purchase-panel">
                        <div>
                            <span>Preço</span>
                            <strong>R$ <?= number_format((float) $game['preco'], 2, ',', '.') ?></strong>
                        </div>

                        <?php if ((int) $game['estoque'] <= 0): ?>
                            <button type="button" disabled>Produto esgotado</button>
                        <?php elseif ($usuario): ?>
                            <form method="post" action="comprar.php">
                                <input type="hidden" name="csrf_token" value="<?= hJogo(gerarTokenCsrf()) ?>">
                                <input type="hidden" name="id" value="<?= (int) $game['id'] ?>">
                                <button type="submit">Comprar agora</button>
                            </form>
                        <?php else: ?>
                            <a class="button" href="login.php?redirect=<?= rawurlencode('jogo.php?id=' . (int) $game['id']) ?>">Entrar para comprar</a>
                        <?php endif; ?>
                        <p>A compra reserva uma unidade e atualiza o estoque imediatamente.</p>
                    </div>
                </div>
            </article>
        <?php else: ?>
            <div class="empty-state">
                <h1>Jogo não encontrado</h1>
                <p>O item solicitado não existe ou foi removido do catálogo.</p>
                <a class="button" href="index.php#catalogo">Ver catálogo</a>
            </div>
        <?php endif; ?>
    </main>

    <footer class="site-footer">
        <div class="container footer-content">
            <div class="footer-brand">
                <img class="brand-logo footer-logo" src="admin/public/imagens/logo.png" alt="Nintendo">
                <p>Encontre sua próxima aventura.</p>
            </div>
            <div class="footer-links" aria-label="Links do rodapé">
                <a href="index.php#catalogo">Catálogo</a>
                <a href="<?= $usuario ? 'logout.php' : 'login.php' ?>"><?= $usuario ? 'Sair' : 'Entrar' ?></a>
            </div>
            <p class="footer-legal">Projeto demonstrativo. Nintendo e suas franquias pertencem aos respectivos titulares.</p>
        </div>
    </footer>
</body>
</html>
