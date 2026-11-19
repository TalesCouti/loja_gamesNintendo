<?php if (!defined('MVC_RENDER')) { http_response_code(403); exit; } ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#e60012">
    <title>Gerenciar usuários | Nintendo Store</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
    <a class="skip-link" href="#conteudo">Ir para o conteúdo</a>
    <header class="site-header">
        <nav class="nav admin-nav" aria-label="Navegação administrativa">
            <a class="brand" href="../../index.php" aria-label="Nintendo Store - página inicial"><img class="brand-logo" src="../public/imagens/logo.png" alt="" aria-hidden="true"><span class="brand-store">Store</span></a>
            <div class="nav-links">
                <a class="nav-link" href="../../index.php">Ver loja</a>
                <a class="nav-link" href="ver_games.php">Jogos</a>
                <a class="nav-link" aria-current="page" href="ver_usuarios.php">Usuários</a>
                <a class="nav-link" href="gerenciar_taxonomia.php?tipo=categorias">Categorias</a>
                <a class="nav-link" href="gerenciar_taxonomia.php?tipo=plataformas">Plataformas</a>
                <a class="nav-link" href="pedidos.php">Pedidos</a>
                <a class="nav-link nav-primary" href="../../logout.php">Sair</a>
            </div>
        </nav>
    </header>

    <main class="container page" id="conteudo">
        <header class="page-header">
            <div>
                <p class="eyebrow">Acesso e permissões</p>
                <h1 class="page-title">Usuários</h1>
                <p class="page-intro">Gerencie clientes e administradores da loja.</p>
            </div>
            <a class="button" href="cadastrar_usuario.php">Cadastrar usuário</a>
        </header>

        <?php if (isset($_GET['cadastrado'])): ?>
            <div class="message" role="status">Usuário cadastrado com sucesso.</div>
        <?php elseif (isset($_GET['editado'])): ?>
            <div class="message" role="status">Usuário atualizado com sucesso.</div>
        <?php elseif (isset($_GET['deletado'])): ?>
            <div class="message" role="status">Usuário excluído com sucesso.</div>
        <?php elseif (is_array($mensagemSessao)): ?>
            <div class="message message-error" role="alert"><?= hUsuario((string) ($mensagemSessao['texto'] ?? 'Não foi possível concluir a ação.')) ?></div>
        <?php endif; ?>

        <div class="table-shell">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Telefone</th>
                        <th>Perfil</th>
                        <th>Cadastrado em</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><div class="table-title"><?= hUsuario($usuario['nome']) ?></div></td>
                            <td><?= hUsuario($usuario['email']) ?></td>
                            <td><?= hUsuario((string) ($usuario['telefone'] ?: 'Não informado')) ?></td>
                            <td><span class="role-badge role-<?= hUsuario($usuario['tipo']) ?>"><?= $usuario['tipo'] === 'admin' ? 'Administrador' : 'Cliente' ?></span></td>
                            <td><?= date('d/m/Y', strtotime((string) $usuario['created_at'])) ?></td>
                            <td>
                                <div class="row-actions">
                                    <a class="button button-secondary" href="editar_usuario.php?id=<?= (int) $usuario['id'] ?>">Editar</a>
                                    <?php if ((int) $usuario['id'] !== (int) $_SESSION['usuario_id']): ?>
                                        <form method="post" action="../controllers/deletar_usuario.php" onsubmit="return confirm('Deseja excluir este usuário?')">
                                            <input type="hidden" name="csrf_token" value="<?= hUsuario(gerarTokenCsrf()) ?>">
                                            <input type="hidden" name="id" value="<?= (int) $usuario['id'] ?>">
                                            <button class="button button-danger" type="submit">Excluir</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
