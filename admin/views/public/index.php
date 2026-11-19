<?php if (!defined('MVC_RENDER')) { http_response_code(403); exit; } ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Explore jogos para Nintendo Switch e encontre sua próxima aventura.">
    <meta name="theme-color" content="#e60012">
    <title>Nintendo Store | Jogue do seu jeito</title>
    <link rel="preload" as="image" href="admin/public/imagens/zelda-tears-of-the-kingdom.webp">
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
                <a class="nav-link" href="#catalogo">Catálogo</a>
                <a class="nav-link" href="carrinho.php">Carrinho</a>
                <?php if ($usuario): ?>
                    <a class="nav-link" href="amigos.php">Amigos</a>
                    <a class="nav-link" href="presentes.php">Presentes</a>
                    <span class="nav-user">Olá, <?= h(primeiroNomeLoja($usuario)) ?></span>
                    <?php if ($usuario['tipo'] === 'admin'): ?>
                        <a class="nav-link" href="admin/views/ver_games.php">Painel</a>
                    <?php endif; ?>
                    <a class="nav-link nav-primary" href="logout.php">Sair</a>
                <?php else: ?>
                    <a class="nav-link" href="registrar.php">Criar conta</a>
                    <a class="nav-link nav-primary" href="login.php">Entrar</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>
    <main id="conteudo">
        <section class="storefront-hero" aria-labelledby="destaque-titulo">
            <div class="container store-hero-stage">
                <div class="store-hero-content">
                    <div class="store-hero-copy">
                        <p class="store-kicker">Destaque Nintendo Switch</p>
                        <h1 id="destaque-titulo">Explore Hyrule.</h1>
                        <p>Descubra as terras e os céus de Zelda: Tears of the Kingdom no Nintendo Switch.</p>
                        <a class="button store-hero-button" href="#catalogo">Ver jogos</a>
                    </div>
                </div>
                <div class="store-hero-media">
                    <img src="admin/public/imagens/zelda-tears-of-the-kingdom.webp" width="1200" height="675" alt="Link contempla as ilhas flutuantes de Hyrule em The Legend of Zelda: Tears of the Kingdom">
                </div>
            </div>
        </section>

        <section class="section catalog-section" id="catalogo">
            <div class="container">
                <?php if (isset($_GET['acesso'])): ?>
                    <div class="message message-error" role="alert">Esta área é exclusiva para administradores.</div>
                <?php endif; ?>
                <div class="section-heading-row">
                    <div class="section-heading">
                        <h2>Jogos disponíveis</h2>
                        <p>Escolha um jogo para você ou envie de presente para um amigo.</p>
                    </div>
                    <div class="catalog-count" aria-label="<?= $totalGames ?> jogos disponíveis">
                        <strong><?= $totalGames ?></strong>
                        <span><?= $totalGames === 1 ? 'jogo' : 'jogos' ?></span>
                    </div>
                </div>

                <?php if ($games): ?>
                    <div class="game-grid">
                        <?php foreach ($games as $index => $game): ?>
                            <article class="game-card">
                                <a class="game-card-link" href="jogo.php?id=<?= (int) $game['id'] ?>">
                                    <div class="game-cover-wrap">
                                        <img class="game-cover" src="admin/public/imagens/<?= rawurlencode(imagemGameOuPlaceholder($game['imagem'] ?? '')) ?>" alt="" loading="<?= $index < 3 ? 'eager' : 'lazy' ?>" width="1200" height="675">
                                    </div>
                                    <div class="game-card-body">
                                        <div class="game-card-copy">
                                            <h3><?= h($game['titulo']) ?></h3>
                                            <p><?= h($game['descricao'] ?: 'Uma nova experiência espera por você.') ?></p>
                                        </div>
                                        <div class="game-card-footer">
                                            <div>
                                                <strong class="price">R$ <?= number_format((float) $game['preco'], 2, ',', '.') ?></strong>
                                                <span class="card-stock <?= (int) $game['estoque'] > 0 ? '' : 'out-of-stock' ?>">
                                                    <?= (int) $game['estoque'] > 0 ? (int) $game['estoque'] . ' em estoque' : 'Esgotado' ?>
                                                </span>
                                            </div>
                                            <span class="card-action">Ver detalhes</span>
                                        </div>
                                    </div>
                                </a>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <h2>Catálogo em preparação</h2>
                        <p>Os jogos aparecerão aqui assim que forem cadastrados.</p>
                        <?php if ($usuario && $usuario['tipo'] === 'admin'): ?>
                            <a class="button" href="admin/views/cadastrar_game.php">Cadastrar jogo</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="container footer-content">
            <div class="footer-brand">
                <img class="brand-logo footer-logo" src="admin/public/imagens/logo.png" alt="Nintendo">
                <p>Encontre sua próxima aventura.</p>
            </div>
            <div class="footer-links" aria-label="Links do rodapé">
                <a href="#catalogo">Catálogo</a>
                <a href="carrinho.php">Carrinho</a>
                <?php if ($usuario): ?><a href="amigos.php">Amigos</a><a href="presentes.php">Presentes</a><?php endif; ?>
                <a href="<?= $usuario ? 'logout.php' : 'login.php' ?>"><?= $usuario ? 'Sair' : 'Entrar' ?></a>
            </div>
            <p class="footer-legal">Projeto demonstrativo. Nintendo e suas franquias pertencem aos respectivos titulares.</p>
        </div>
    </footer>
</body>
</html>
