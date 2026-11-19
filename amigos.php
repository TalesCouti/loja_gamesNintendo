<?php
require_once __DIR__ . '/admin/config/autenticacao.php';
exigirUsuario('login.php?redirect=amigos.php');
require_once __DIR__ . '/admin/models/social.php';
$usuario = usuarioAtual();
$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!tokenCsrfValido($_POST['csrf_token'] ?? null)) {
        $erro = 'Sessão expirada. Atualize a página e tente novamente.';
    } else {
        try {
            $acao = (string) ($_POST['acao'] ?? '');
            if ($acao === 'solicitar') {
                $email = strtolower(trim((string) ($_POST['email'] ?? '')));
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { throw new RuntimeException('Informe um e-mail válido.'); }
                $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = :email LIMIT 1');
                $stmt->execute([':email' => $email]);
                $outroId = (int) $stmt->fetchColumn();
                if (!$outroId || $outroId === $usuario['id']) { throw new RuntimeException('Não foi possível enviar a solicitação para esse e-mail.'); }
                [$a, $b] = amizadePar($usuario['id'], $outroId);
                $stmt = $pdo->prepare('INSERT INTO amizades (usuario_a, usuario_b, solicitante_id) VALUES (:a, :b, :solicitante)');
                $stmt->execute([':a' => $a, ':b' => $b, ':solicitante' => $usuario['id']]);
            } elseif (in_array($acao, ['aceitar', 'recusar', 'cancelar', 'remover'], true)) {
                $outroId = (int) ($_POST['usuario_id'] ?? 0);
                if ($outroId <= 0 || $outroId === $usuario['id']) { throw new RuntimeException('Usuário inválido.'); }
                [$a, $b] = amizadePar($usuario['id'], $outroId);
                if ($acao === 'aceitar') {
                    $stmt = $pdo->prepare("UPDATE amizades SET status = 'aceita' WHERE usuario_a = :a AND usuario_b = :b AND status = 'pendente' AND solicitante_id = :outro");
                } else {
                    $condicao = $acao === 'remover' ? "status = 'aceita'" : ($acao === 'cancelar' ? "status = 'pendente' AND solicitante_id = :ator" : "status = 'pendente' AND solicitante_id = :outro");
                    $stmt = $pdo->prepare("DELETE FROM amizades WHERE usuario_a = :a AND usuario_b = :b AND $condicao");
                }
                $dados = [':a' => $a, ':b' => $b];
                if (in_array($acao, ['aceitar', 'recusar'], true)) { $dados[':outro'] = $outroId; }
                if ($acao === 'cancelar') { $dados[':ator'] = $usuario['id']; }
                $stmt->execute($dados);
                if ($stmt->rowCount() !== 1) { throw new RuntimeException('Solicitação não encontrada ou já atualizada.'); }
            } else { throw new RuntimeException('Ação inválida.'); }
            header('Location: amigos.php?msg=ok'); exit;
        } catch (PDOException $e) {
            $erro = $e->getCode() === '23000' ? 'Já existe uma solicitação ou amizade com esse usuário.' : 'Não foi possível atualizar a lista de amigos.';
        } catch (RuntimeException $e) { $erro = $e->getMessage(); }
    }
}
$stmt = $pdo->prepare('SELECT a.usuario_a, a.usuario_b, a.solicitante_id, a.status, u.id AS outro_id, u.nome, u.email FROM amizades a JOIN usuarios u ON u.id = IF(a.usuario_a = :id1, a.usuario_b, a.usuario_a) WHERE a.usuario_a = :id2 OR a.usuario_b = :id3 ORDER BY a.created_at DESC');
$stmt->execute([':id1' => $usuario['id'], ':id2' => $usuario['id'], ':id3' => $usuario['id']]);
$relacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
function hAmigos(string $texto): string { return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Amigos | Nintendo Store</title><link rel="stylesheet" href="admin/public/css/style.css"></head>
<body class="storefront-page"><a class="skip-link" href="#conteudo">Ir para o conteúdo</a><header class="site-header"><nav class="nav" aria-label="Navegação principal"><a class="brand store-brand" href="index.php"><img class="brand-logo" src="admin/public/imagens/logo.png" alt="Nintendo"><span class="brand-store">Store</span></a><div class="nav-links"><a class="nav-link" href="index.php#catalogo">Catálogo</a><a class="nav-link" href="carrinho.php">Carrinho</a><a class="nav-link" href="presentes.php">Presentes</a><a class="nav-link nav-primary" href="logout.php">Sair</a></div></nav></header>
<main class="container page" id="conteudo"><div class="page-header"><div><p class="eyebrow">Jogue junto</p><h1 class="page-title">Amigos</h1><p class="page-intro">Envie solicitações por e-mail. Depois que a pessoa aceitar, você poderá enviar jogos de presente.</p></div></div>
<?php if ($erro): ?><div class="message message-error" role="alert"><?= hAmigos($erro) ?></div><?php elseif (isset($_GET['msg'])): ?><div class="message" role="status">Lista de amigos atualizada.</div><?php endif; ?>
<form class="social-form" method="post"><input type="hidden" name="csrf_token" value="<?= hAmigos(gerarTokenCsrf()) ?>"><input type="hidden" name="acao" value="solicitar"><label for="email-amigo">E-mail do amigo</label><div class="social-form-row"><input id="email-amigo" type="email" name="email" placeholder="amigo@exemplo.com" required><button type="submit">Enviar solicitação</button></div></form>
<div class="social-columns"><section><h2>Seus amigos</h2><?php $temAmigo = false; foreach ($relacoes as $relacao): if ($relacao['status'] !== 'aceita') continue; $temAmigo = true; ?><article class="social-row"><div><strong><?= hAmigos($relacao['nome']) ?></strong><span><?= hAmigos($relacao['email']) ?></span></div><form method="post"><input type="hidden" name="csrf_token" value="<?= hAmigos(gerarTokenCsrf()) ?>"><input type="hidden" name="acao" value="remover"><input type="hidden" name="usuario_id" value="<?= (int) $relacao['outro_id'] ?>"><button class="button-text" type="submit">Remover</button></form></article><?php endforeach; if (!$temAmigo): ?><p class="field-help">Nenhum amigo confirmado ainda.</p><?php endif; ?></section>
<section><h2>Solicitações</h2><?php $temPedido = false; foreach ($relacoes as $relacao): if ($relacao['status'] !== 'pendente') continue; $temPedido = true; $recebida = (int) $relacao['solicitante_id'] !== $usuario['id']; ?><article class="social-row"><div><strong><?= hAmigos($relacao['nome']) ?></strong><span><?= $recebida ? 'Quer adicionar você' : 'Aguardando resposta' ?></span></div><div class="social-buttons"><?php foreach ($recebida ? ['aceitar' => 'Aceitar', 'recusar' => 'Recusar'] : ['cancelar' => 'Cancelar'] as $acao => $rotulo): ?><form method="post"><input type="hidden" name="csrf_token" value="<?= hAmigos(gerarTokenCsrf()) ?>"><input type="hidden" name="acao" value="<?= $acao ?>"><input type="hidden" name="usuario_id" value="<?= (int) $relacao['outro_id'] ?>"><button class="<?= $acao === 'aceitar' ? 'button-secondary' : 'button-text' ?>" type="submit"><?= $rotulo ?></button></form><?php endforeach; ?></div></article><?php endforeach; if (!$temPedido): ?><p class="field-help">Nenhuma solicitação pendente.</p><?php endif; ?></section></div></main></body></html>
