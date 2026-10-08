<?php
require_once __DIR__ . '/admin/models/games.php';
require_once __DIR__ . '/admin/config/imagens.php';

$gameModel = new Game();
$games = $gameModel->listarGames();
$totalGames = count($games);

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
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
                <span class="brand-lockup">Nintendo</span>
                <span class="brand-store">Store</span>
            </a>
            <div class="nav-links">
                <a class="nav-link" href="#catalogo">Catálogo</a>
                <a class="nav-link nav-primary" href="login.php">Painel</a>
            </div>
        </nav>
    </header>

    <main id="conteudo">
        <section class="storefront-hero" aria-labelledby="destaque-titulo">
            <div class="container store-hero-stage">
                <div class="store-hero-content">
                    <div class="store-hero-copy">
                        <p class="store-kicker">Destaque Nintendo Switch</p>
                        <h1 id="destaque-titulo">A aventura está lá em cima.</h1>
                        <p>Explore Hyrule por terra e pelos céus em uma jornada feita para Nintendo Switch.</p>
                        <a class="button store-hero-button" href="#catalogo">Ver jogos</a>
                    </div>
                </div>
                <div class="store-hero-media">
                    <img src="admin/public/imagens/zelda-tears-of-the-kingdom.webp" width="1200" height="675" alt="Link contempla as ilhas flutuantes de Hyrule em The Legend of Zelda: Tears of the Kingdom">
                </div>
            </div>
        </section>

        <section class="store-benefits" aria-label="Vantagens da loja">
            <div class="container benefit-list">
                <p><strong>Jogos para todos</strong><span>Aventura, corrida, estratégia e diversão em família.</span></p>
                <p><strong>Catálogo Nintendo</strong><span>Grandes séries reunidas em um só lugar.</span></p>
                <p><strong>Estoque atualizado</strong><span>Consulte a disponibilidade direto na vitrine.</span></p>
            </div>
        </section>

        <section class="section catalog-section" id="catalogo">
            <div class="container">
                <div class="section-heading">
                    <h2>Escolha sua próxima aventura</h2>
                    <p><?= $totalGames ?> <?= $totalGames === 1 ? 'jogo disponível' : 'jogos disponíveis' ?> no catálogo.</p>
                </div>

                <?php if ($games): ?>
                    <div class="game-grid">
                        <?php foreach ($games as $index => $game): ?>
                            <article class="game-card<?= $index === 0 ? ' game-card-featured' : '' ?>">
                                <div class="game-cover-wrap">
                                    <img class="game-cover" src="admin/public/imagens/<?= rawurlencode(imagemGameOuPlaceholder($game['imagem'] ?? '')) ?>" alt="Imagem de <?= h($game['titulo']) ?>" loading="<?= $index < 2 ? 'eager' : 'lazy' ?>" width="1200" height="675">
                                </div>
                                <div class="game-card-body">
                                    <div class="game-card-copy">
                                        <span class="game-platform">Nintendo Switch</span>
                                        <h3><?= h($game['titulo']) ?></h3>
                                        <p><?= h($game['descricao'] ?: 'Uma nova experiência espera por você.') ?></p>
                                        <div class="game-meta">
                                            <span><?= h($game['categoria'] ?: 'Sem categoria') ?></span>
                                            <span><?= (int) $game['estoque'] ?> em estoque</span>
                                        </div>
                                    </div>
                                    <strong class="price">R$ <?= number_format((float) $game['preco'], 2, ',', '.') ?></strong>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <h2>Catálogo em preparação</h2>
                        <p>Os jogos aparecerão aqui assim que forem cadastrados.</p>
                        <a class="button" href="login.php">Acessar painel</a>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="container footer-content">
            <span class="brand-lockup">Nintendo</span>
            <p>Projeto demonstrativo de uma loja de jogos. Nintendo e suas franquias pertencem aos respectivos titulares.</p>
        </div>
    </footer>
</body>
</html>
