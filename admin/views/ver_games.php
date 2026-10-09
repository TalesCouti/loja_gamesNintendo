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
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#e60012">
    <title>Gerenciar jogos | Nintendo Store</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
    <a class="skip-link" href="#conteudo">Ir para o conteúdo</a>
    <header class="site-header">
        <nav class="nav admin-nav" aria-label="Navegação administrativa">
            <a class="brand" href="../../index.php" aria-label="Nintendo Store - página inicial"><img class="brand-logo" src="../public/imagens/logo.png" alt="" aria-hidden="true"><span class="brand-store">Store</span></a>
            <div class="nav-links">
                <a class="nav-link" href="../../index.php">Ver loja</a>
                <a class="nav-link" aria-current="page" href="ver_games.php">Jogos</a>
                <a class="nav-link" href="ver_usuarios.php">Usuários</a>
                <a class="nav-link nav-primary" href="../../logout.php">Sair</a>
            </div>
        </nav>
    </header>

    <main class="container page" id="conteudo">
        <header class="page-header">
            <div>
                <h1 class="page-title">Catálogo de jogos</h1>
                <p class="page-intro">Acompanhe preços, estoque e informações dos jogos cadastrados.</p>
            </div>
            <a class="button" href="cadastrar_game.php">Cadastrar jogo</a>
        </header>

        <?php if (isset($_GET['deletado'])): ?>
            <div class="message" role="status">Jogo excluído com sucesso.</div>
        <?php elseif (isset($_GET['editado'])): ?>
            <div class="message" role="status">Jogo atualizado com sucesso.</div>
        <?php endif; ?>

        <?php if ($games): ?>
            <div class="table-shell">
                <table>
                    <thead>
                        <tr>
                            <th>Imagem</th>
                            <th>Jogo</th>
                            <th>Preço</th>
                            <th>Estoque</th>
                            <th>Categoria</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($games as $game): ?>
                            <tr>
                                <td>
                                    <img class="table-cover" src="../public/imagens/<?= rawurlencode(imagemGameOuPlaceholder($game['imagem'] ?? '')) ?>" alt="Capa de <?= h($game['titulo']) ?>" loading="lazy" width="120" height="90">
                                </td>
                                <td>
                                    <div class="table-title"><?= h($game['titulo']) ?></div>
                                    <div class="table-description"><?= h($game['descricao'] ?: 'Sem descrição') ?></div>
                                </td>
                                <td class="price">R$ <?= number_format((float) $game['preco'], 2, ',', '.') ?></td>
                                <td><span class="stock"><?= (int) $game['estoque'] ?> unidades</span></td>
                                <td><span class="category"><?= h($game['categoria'] ?: 'Sem categoria') ?></span></td>
                                <td>
                                    <div class="row-actions">
                                        <a class="button button-secondary" href="editar_games.php?id=<?= (int) $game['id'] ?>">Editar</a>
                                        <form method="post" action="../controllers/deletar_game.php" onsubmit="return confirm('Deseja excluir este jogo?')">
                                            <input type="hidden" name="csrf_token" value="<?= h(gerarTokenCsrf()) ?>">
                                            <input type="hidden" name="id" value="<?= (int) $game['id'] ?>">
                                            <button class="button button-danger" type="submit">Excluir</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h2>Nenhum jogo cadastrado</h2>
                <p>Comece adicionando o primeiro título ao catálogo.</p>
                <a class="button" href="cadastrar_game.php">Cadastrar jogo</a>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
