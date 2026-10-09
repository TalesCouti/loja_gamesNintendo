<?php
require_once __DIR__ . '/admin/config/conexao.php';
require_once __DIR__ . '/admin/config/autenticacao.php';

iniciarSessaoSegura();

function destinoSeguroAposLogin(?string $destino): string
{
    if (!is_string($destino) || $destino === '') {
        return 'index.php';
    }

    $partes = parse_url($destino);
    if ($partes === false || isset($partes['scheme']) || isset($partes['host'])) {
        return 'index.php';
    }

    $caminho = ltrim((string) ($partes['path'] ?? ''), '/');
    if ($caminho === 'jogo.php') {
        parse_str((string) ($partes['query'] ?? ''), $consulta);
        $id = (int) ($consulta['id'] ?? 0);

        return $id > 0 ? 'jogo.php?id=' . $id : 'index.php';
    }

    return $caminho === 'index.php' ? 'index.php' : 'index.php';
}

$destinoSolicitado = (string) ($_POST['redirect'] ?? $_GET['redirect'] ?? '');

if (usuarioAutenticado()) {
    header('Location: ' . (
        usuarioAdministradorAutenticado()
            ? 'admin/views/ver_games.php'
            : destinoSeguroAposLogin($destinoSolicitado)
    ));
    exit;
}

$mensagem = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $senhaInformada = (string) ($_POST['senha'] ?? '');
    $token = $_POST['csrf_token'] ?? null;

    if (
        tokenCsrfValido(is_string($token) ? $token : null)
        && filter_var($email, FILTER_VALIDATE_EMAIL)
        && $senhaInformada !== ''
        && autenticarUsuario($pdo, $email, $senhaInformada)
    ) {
        header('Location: ' . (
            usuarioAdministradorAutenticado()
                ? 'admin/views/ver_games.php'
                : destinoSeguroAposLogin($destinoSolicitado)
        ));
        exit;
    }

    $mensagem = 'E-mail ou senha inválidos.';
}
?>
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
                <?php endif; ?>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(gerarTokenCsrf(), ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="redirect" value="<?= htmlspecialchars(destinoSeguroAposLogin($destinoSolicitado), ENT_QUOTES, 'UTF-8') ?>">
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
                    <a href="index.php">Voltar para a loja</a>
                </p>
            </form>
        </div>
    </main>
</body>
</html>
