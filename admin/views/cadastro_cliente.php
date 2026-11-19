<?php
if (!defined('MVC_RENDER')) { http_response_code(403); exit; }
function hCadastro(string $texto): string { return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar conta | Nintendo Store</title>
    <link rel="stylesheet" href="admin/public/css/style.css">
    <script src="admin/public/js/registro.js" defer></script>
</head>
<body class="storefront-page">
    <a class="skip-link" href="#conteudo">Ir para o conteúdo</a>
    <main class="auth-page" id="conteudo">
        <div class="auth-layout">
            <section>
                <a class="brand" href="index.php"><img class="brand-logo" src="admin/public/imagens/logo.png" alt="Nintendo"><span class="brand-store">Store</span></a>
                <h1 class="auth-title" style="margin-top: 36px;">Crie sua conta.</h1>
                <p class="auth-copy">Salve seus jogos no carrinho, acompanhe pedidos e envie presentes para amigos.</p>
            </section>
            <form class="auth-card" method="post" action="registrar.php" data-registro>
                <?php if ($erroCadastro): ?><div class="message message-error" role="alert"><?= hCadastro($erroCadastro) ?></div><?php endif; ?>
                <input type="hidden" name="csrf_token" value="<?= hCadastro(gerarTokenCsrf()) ?>">
                <div class="field"><label for="nome">Nome completo</label><input id="nome" name="nome" type="text" maxlength="100" autocomplete="name" value="<?= hCadastro($dadosCadastro['nome']) ?>" required></div>
                <div class="field"><label for="email">E-mail</label><input id="email" name="email" type="email" maxlength="150" autocomplete="email" value="<?= hCadastro($dadosCadastro['email']) ?>" required></div>
                <div class="field"><label for="telefone">Telefone com DDD</label><input id="telefone" name="telefone" type="tel" maxlength="20" autocomplete="tel" value="<?= hCadastro($dadosCadastro['telefone']) ?>" required><p class="field-help">Exemplo: (11) 99999-9999.</p></div>
                <div class="field"><label for="senha">Senha</label><input id="senha" name="senha" type="password" minlength="8" autocomplete="new-password" required><p class="field-help">Use pelo menos 8 caracteres.</p></div>
                <div class="field"><label for="confirmar_senha">Confirme a senha</label><input id="confirmar_senha" name="confirmar_senha" type="password" minlength="8" autocomplete="new-password" aria-describedby="erro-senha" required><p class="field-help" id="erro-senha" role="status"></p></div>
                <button type="submit">Criar conta</button>
                <p class="field-help auth-switch">Já tem conta? <a href="login.php">Entrar</a></p>
            </form>
        </div>
    </main>
</body>
</html>
