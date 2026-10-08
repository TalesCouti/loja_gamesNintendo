<?php
require_once __DIR__ . '/admin/config/conexao.php';

// Query para buscar todos os games.
$sql = 'SELECT * FROM games';
$stmt = $pdo->prepare($sql);
$stmt->execute();
$games = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#e60012">
    <title>Status da loja | Nintendo Store</title>
    <link rel="stylesheet" href="admin/public/css/style.css">
</head>
<body>
    <a class="skip-link" href="#conteudo">Ir para o conteúdo</a>
    <header class="site-header">
        <nav class="nav" aria-label="Navegação principal">
            <a class="brand" href="index.php"><span class="brand-lockup">Nintendo</span><span class="brand-store">Store</span></a>
            <div class="nav-links">
                <a class="nav-link" href="index.php">Ver loja</a>
                <a class="nav-link" href="admin/views/ver_games.php">Painel</a>
            </div>
        </nav>
    </header>

    <main class="container page" id="conteudo">
        <header class="page-header">
            <div>
                <span class="connection-status">Banco conectado</span>
                <h1 class="page-title" style="margin-top: 18px;">Teste de catálogo</h1>
                <p class="page-intro">A consulta foi executada diretamente no banco loja_games.</p>
            </div>
            <a class="button" href="admin/views/cadastrar_game.php">Cadastrar jogo</a>
        </header>

        <?php if (count($games) > 0): ?>
            <div class="table-shell">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Descrição</th>
                            <th>Preço</th>
                            <th>Estoque</th>
                            <th>Categoria</th>
                            <th>Imagem</th>
                        </tr>
                    </thead>
                    <tbody>
                <?php foreach ($games as $game): ?>
                    <tr>
                        <td><?= htmlspecialchars((string) $game['id'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="table-title"><?= htmlspecialchars($game['titulo'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="table-description"><?= htmlspecialchars($game['descricao'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                        <td>R$ <?= number_format((float) $game['preco'], 2, ',', '.') ?></td>
                        <td><span class="stock"><?= htmlspecialchars((string) $game['estoque'], ENT_QUOTES, 'UTF-8') ?></span></td>
                        <td><span class="category"><?= htmlspecialchars($game['categoria'] ?? '', ENT_QUOTES, 'UTF-8') ?></span></td>
                        <td><?= htmlspecialchars($game['imagem'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h2>Nenhum jogo cadastrado</h2>
                <p>A conexão está ativa, mas a tabela ainda está vazia.</p>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
