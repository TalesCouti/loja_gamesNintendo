<?php
require_once __DIR__ . '/../../config/autenticacao.php';
exigirUsuario('login.php?redirect=amigos.php');
require_once __DIR__ . '/../../models/social.php';
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

define('MVC_RENDER', true);
require __DIR__ . '/../../views/public/amigos.php';
