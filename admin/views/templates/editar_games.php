<?php if (!defined('MVC_RENDER')) { http_response_code(403); exit; } ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#e60012">
    <title>Editar jogo | Nintendo Store</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
    <a class="skip-link" href="#conteudo">Ir para o conteúdo</a>
    <header class="site-header">
        <nav class="nav admin-nav" aria-label="Navegação administrativa">
            <a class="brand" href="../../index.php" aria-label="Nintendo Store - página inicial"><img class="brand-logo" src="../public/imagens/logo.png" alt="" aria-hidden="true"><span class="brand-store">Store</span></a>
            <div class="nav-links"><a class="nav-link" href="ver_games.php">Jogos</a><a class="nav-link" href="ver_usuarios.php">Usuários</a><a class="nav-link nav-primary" href="../../logout.php">Sair</a></div>
        </nav>
    </header>

    <main class="container page" id="conteudo">
        <?php if ($dados): ?>
            <div class="form-layout">
                <section>
                    <h1 class="page-title">Editar jogo</h1>
                    <p class="page-intro">Atualize as informações de <?= htmlspecialchars($dados['titulo'], ENT_QUOTES, 'UTF-8') ?>.</p>
                </section>
                <section class="form-card">
                    <?php if ($mensagem !== ''): ?>
                        <div class="message message-error" role="alert"><?= htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(gerarTokenCsrf(), ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="id" value="<?= (int) $dados['id'] ?>">
                        <div class="form-grid">
                            <div class="field field-full">
                                <label for="titulo">Título</label>
                                <input id="titulo" type="text" name="titulo" value="<?= htmlspecialchars($dados['titulo'], ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                            <div class="field field-full">
                                <label for="descricao">Descrição</label>
                                <textarea id="descricao" name="descricao" rows="4" required><?= htmlspecialchars($dados['descricao'], ENT_QUOTES, 'UTF-8') ?></textarea>
                            </div>
                            <div class="field">
                                <label for="preco">Preço</label>
                                <input id="preco" type="number" step="0.01" min="0" name="preco" value="<?= htmlspecialchars((string) $dados['preco'], ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                            <div class="field">
                                <label for="estoque">Estoque</label>
                                <input id="estoque" type="number" min="0" name="estoque" value="<?= (int) $dados['estoque'] ?>" required>
                            </div>
                            <div class="field field-full">
                                <label for="categoria">Categoria</label>
                                <select id="categoria" name="categoria" required><option value="">Selecione</option><?php foreach ($categorias as $categoria): ?><option value="<?= htmlspecialchars($categoria['nome'], ENT_QUOTES, 'UTF-8') ?>" <?= ($dados['categoria'] ?? '') === $categoria['nome'] ? 'selected' : '' ?>><?= htmlspecialchars($categoria['nome'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select>
                            </div>
                            <div class="field field-full">
                                <label for="plataforma">Plataforma</label>
                                <select id="plataforma" name="plataforma" required><option value="">Selecione</option><?php foreach ($plataformas as $plataforma): ?><option value="<?= htmlspecialchars($plataforma['nome'], ENT_QUOTES, 'UTF-8') ?>" <?= ($dados['plataforma'] ?? '') === $plataforma['nome'] ? 'selected' : '' ?>><?= htmlspecialchars($plataforma['nome'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select>
                            </div>
                            <div class="field field-full">
                                <label for="imagem">Imagem do jogo</label>
                                <img class="image-preview" src="../public/imagens/<?= rawurlencode(imagemGameOuPlaceholder($dados['imagem'] ?? '')) ?>" alt="Imagem atual de <?= htmlspecialchars($dados['titulo'], ENT_QUOTES, 'UTF-8') ?>">
                                <input id="imagem" type="file" name="imagem" accept="image/jpeg,image/png,image/webp">
                                <p class="field-help">Opcional. Escolha um arquivo somente se quiser substituir a imagem atual.</p>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit">Salvar alterações</button>
                            <a class="button button-secondary" href="ver_games.php">Cancelar</a>
                        </div>
                    </form>
                </section>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h1>Jogo não encontrado</h1>
                <p>O item solicitado não existe ou foi removido.</p>
                <a class="button" href="ver_games.php">Voltar ao catálogo</a>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
