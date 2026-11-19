<?php if (!defined('MVC_RENDER')) { http_response_code(403); exit; } ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Entre na Nintendo Store para acessar sua conta e comprar jogos.">
    <meta name="theme-color" content="#e60012">
    <title>Entrar | Nintendo Store</title>
    <link rel="stylesheet" href="admin/public/css/style.css">
</head>
<body>
    <a class="skip-link" href="#conteudo">Ir para o conteúdo</a>
    <main class="auth-page" id="conteudo">
        <div class="auth-layout">
            <section>
                <a class="brand" href="index.php" aria-label="Nintendo Store - página inicial"><img class="brand-logo" src="admin/public/imagens/logo.png" alt="" aria-hidden="true"><span class="brand-store">Store</span></a>
                <h1 class="auth-title" style="margin-top: 48px;">Bem-vindo de volta.</h1>
                <p class="auth-copy">Entre para comprar seus jogos ou, se você administra a loja, organizar catálogo e usuários.</p>
            </section>

            <form class="auth-card" method="post" action="login.php">
                <?php if ($mensagem !== ''): ?>
                    <div class="message message-error" role="alert"><?= htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') ?></div>
                <?php elseif (isset($_GET['logout'])): ?>
                    <div class="message" role="status">Sessão encerrada com sucesso.</div>
                <?php elseif (isset($_GET['cadastro'])): ?>
                    <div class="message" role="status">Conta criada. Entre para continuar.</div>
                <?php endif; ?>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(gerarTokenCsrf(), ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="redirect" value="<?= $destinoSolicitado === '' ? '' : htmlspecialchars(destinoSeguroAposLogin($destinoSolicitado), ENT_QUOTES, 'UTF-8') ?>">
                <div class="field">
                    <label for="email">E-mail</label>
                    <input id="email" name="email" type="email" autocomplete="username" placeholder="voce@exemplo.com" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" required autofocus>
                </div>
                <div class="field">
                    <label for="senha-login">Senha</label>
                    <input id="senha-login" name="senha" type="password" autocomplete="current-password" placeholder="Digite sua senha" required>
                </div>
                <button type="submit">Entrar</button>
                <p class="field-help" style="margin-top: 18px; text-align: center;">
                    <a href="registrar.php">Criar conta</a> · <a href="index.php">Voltar para a loja</a>
                </p>
            </form>
        </div>
    </main>
</body>
</html>
