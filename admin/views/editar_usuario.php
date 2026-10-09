<?php
require_once __DIR__ . '/../config/autenticacao.php';
exigirAdministrador('../../login.php');

require_once __DIR__ . '/../models/usuarios.php';

$usuarioModel = new Usuario();
$id = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);
$dados = $usuarioModel->buscarUsuarioPorId($id);
$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!tokenCsrfValido($_POST['csrf_token'] ?? null)) {
            throw new RuntimeException('A sessão do formulário expirou. Tente novamente.');
        }

        $tipo = (string) ($_POST['tipo'] ?? 'user');
        if ($id === (int) $_SESSION['usuario_id'] && $tipo !== 'admin') {
            throw new RuntimeException('Você não pode remover o próprio acesso administrativo.');
        }

        $usuarioModel->editarUsuario($id, [
            'nome' => $_POST['nome'] ?? '',
            'email' => $_POST['email'] ?? '',
            'senha' => $_POST['senha'] ?? '',
            'tipo' => $tipo,
        ]);

        if ($id === (int) $_SESSION['usuario_id']) {
            $_SESSION['usuario_nome'] = trim((string) $_POST['nome']);
            $_SESSION['usuario_email'] = strtolower(trim((string) $_POST['email']));
        }

        header('Location: ver_usuarios.php?editado=1');
        exit;
    } catch (RuntimeException $e) {
        $mensagem = $e->getMessage();
        $dados = array_merge($dados ?: [], [
            'id' => $id,
            'nome' => trim((string) ($_POST['nome'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'tipo' => (string) ($_POST['tipo'] ?? 'user'),
        ]);
    }
}

function hUsuario(string $valor): string
{
    return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#e60012">
    <title>Editar usuário | Nintendo Store</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
    <a class="skip-link" href="#conteudo">Ir para o conteúdo</a>
    <header class="site-header">
        <nav class="nav admin-nav" aria-label="Navegação administrativa">
            <a class="brand" href="../../index.php" aria-label="Nintendo Store - página inicial"><img class="brand-logo" src="../public/imagens/logo.png" alt="" aria-hidden="true"><span class="brand-store">Store</span></a>
            <div class="nav-links"><a class="nav-link" href="ver_usuarios.php">Usuários</a><a class="nav-link nav-primary" href="../../logout.php">Sair</a></div>
        </nav>
    </header>

    <main class="container page" id="conteudo">
        <?php if ($dados): ?>
            <div class="form-layout">
                <section>
                    <p class="eyebrow">Administração</p>
                    <h1 class="page-title">Editar usuário</h1>
                    <p class="page-intro">Atualize os dados e o nível de acesso de <?= hUsuario((string) $dados['nome']) ?>.</p>
                </section>
                <section class="form-card">
                    <?php if ($mensagem !== ''): ?>
                        <div class="message message-error" role="alert"><?= hUsuario($mensagem) ?></div>
                    <?php endif; ?>
                    <form method="post" action="editar_usuario.php">
                        <input type="hidden" name="csrf_token" value="<?= hUsuario(gerarTokenCsrf()) ?>">
                        <input type="hidden" name="id" value="<?= (int) $dados['id'] ?>">
                        <div class="form-grid">
                            <div class="field field-full">
                                <label for="nome">Nome</label>
                                <input id="nome" name="nome" type="text" maxlength="100" value="<?= hUsuario((string) $dados['nome']) ?>" required>
                            </div>
                            <div class="field field-full">
                                <label for="email">E-mail</label>
                                <input id="email" name="email" type="email" maxlength="150" value="<?= hUsuario((string) $dados['email']) ?>" required>
                            </div>
                            <div class="field">
                                <label for="senha">Nova senha</label>
                                <input id="senha" name="senha" type="password" minlength="8" autocomplete="new-password">
                                <p class="field-help">Deixe em branco para manter a senha atual.</p>
                            </div>
                            <div class="field">
                                <label for="tipo">Tipo de usuário</label>
                                <select id="tipo" name="tipo" required>
                                    <option value="user" <?= $dados['tipo'] === 'user' ? 'selected' : '' ?>>Cliente</option>
                                    <option value="admin" <?= $dados['tipo'] === 'admin' ? 'selected' : '' ?>>Administrador</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit">Salvar alterações</button>
                            <a class="button button-secondary" href="ver_usuarios.php">Cancelar</a>
                        </div>
                    </form>
                </section>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h1>Usuário não encontrado</h1>
                <p>A conta solicitada não existe ou foi removida.</p>
                <a class="button" href="ver_usuarios.php">Voltar aos usuários</a>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
