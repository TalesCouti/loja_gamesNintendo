<?php if (!defined('MVC_RENDER')) { http_response_code(403); exit; } ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#e60012">
    <title>Cadastrar usuário | Nintendo Store</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
    <a class="skip-link" href="#conteudo">Ir para o conteúdo</a>
    <header class="site-header">
        <nav class="nav admin-nav" aria-label="Navegação administrativa">
            <a class="brand" href="../../index.php" aria-label="Nintendo Store - página inicial"><img class="brand-logo" src="../public/imagens/logo.png" alt="" aria-hidden="true"><span class="brand-store">Store</span></a>
            <div class="nav-links">
                <a class="nav-link" href="ver_games.php">Jogos</a>
                <a class="nav-link" href="ver_usuarios.php">Usuários</a>
                <a class="nav-link nav-primary" href="../../logout.php">Sair</a>
            </div>
        </nav>
    </header>

    <main class="container page" id="conteudo">
        <div class="form-layout">
            <section>
                <p class="eyebrow">Administração</p>
                <h1 class="page-title">Novo usuário</h1>
                <p class="page-intro">Crie uma conta de cliente ou conceda acesso administrativo ao painel.</p>
            </section>

            <section class="form-card">
                <?php if ($mensagem !== ''): ?>
                    <div class="message message-error" role="alert"><?= hUsuario($mensagem) ?></div>
                <?php endif; ?>

                <form method="post" action="cadastrar_usuario.php">
                    <input type="hidden" name="csrf_token" value="<?= hUsuario(gerarTokenCsrf()) ?>">
                    <div class="form-grid">
                        <div class="field field-full">
                            <label for="nome">Nome</label>
                            <input id="nome" name="nome" type="text" maxlength="100" autocomplete="name" value="<?= hUsuario($nome) ?>" required>
                        </div>
                        <div class="field field-full">
                            <label for="email">E-mail</label>
                            <input id="email" name="email" type="email" maxlength="150" autocomplete="email" value="<?= hUsuario($email) ?>" required>
                        </div>
                        <div class="field">
                            <label for="telefone">Telefone</label>
                            <input id="telefone" name="telefone" type="tel" maxlength="20" autocomplete="tel" value="<?= hUsuario($telefone) ?>">
                        </div>
                        <div class="field">
                            <label for="senha">Senha inicial</label>
                            <input id="senha" name="senha" type="password" minlength="8" autocomplete="new-password" required>
                            <p class="field-help">Use pelo menos 8 caracteres.</p>
                        </div>
                        <div class="field">
                            <label for="tipo">Tipo de usuário</label>
                            <select id="tipo" name="tipo" required>
                                <option value="user" <?= $tipo === 'user' ? 'selected' : '' ?>>Cliente</option>
                                <option value="admin" <?= $tipo === 'admin' ? 'selected' : '' ?>>Administrador</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit">Cadastrar usuário</button>
                        <a class="button button-secondary" href="ver_usuarios.php">Cancelar</a>
                    </div>
                </form>
            </section>
        </div>
    </main>
</body>
</html>
