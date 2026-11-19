<?php
require_once __DIR__ . '/../../config/autenticacao.php';
exigirUsuario('login.php?redirect=carrinho.php');
require_once __DIR__ . '/../../models/social.php';
require_once __DIR__ . '/../../config/imagens.php';

$usuario = usuarioAtual();
$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!tokenCsrfValido($_POST['csrf_token'] ?? null)) {
        $erro = 'Sessão expirada. Atualize a página e tente novamente.';
    } else {
        try {
            $acao = (string) ($_POST['acao'] ?? '');
            if ($acao === 'adicionar') {
                adicionarAoCarrinho($pdo, $usuario['id'], (int) ($_POST['game_id'] ?? 0), (int) ($_POST['destinatario_id'] ?? 0), (int) ($_POST['quantidade'] ?? 1));
                header('Location: carrinho.php?msg=adicionado'); exit;
            }
            if ($acao === 'remover' || $acao === 'atualizar') {
                $id = (int) ($_POST['item_id'] ?? 0);
                $quantidade = (int) ($_POST['quantidade'] ?? 0);
                if ($id <= 0 || ($acao === 'atualizar' && ($quantidade < 1 || $quantidade > 99))) {
                    throw new RuntimeException('Item ou quantidade inválida.');
                }
                $stmt = $pdo->prepare('SELECT c.id, c.game_id, c.destinatario_id, g.estoque FROM carrinho_itens c JOIN games g ON g.id = c.game_id WHERE c.id = :id AND c.usuario_id = :usuario');
                $stmt->execute([':id' => $id, ':usuario' => $usuario['id']]);
                $item = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$item) { throw new RuntimeException('Item não encontrado.'); }
                if ($acao === 'atualizar' && $quantidade > (int) $item['estoque']) { throw new RuntimeException('Quantidade indisponível em estoque.'); }
                $stmt = $pdo->prepare($acao === 'remover' ? 'DELETE FROM carrinho_itens WHERE id = :id AND usuario_id = :usuario' : 'UPDATE carrinho_itens SET quantidade = :quantidade WHERE id = :id AND usuario_id = :usuario');
                $dados = [':id' => $id, ':usuario' => $usuario['id']];
                if ($acao === 'atualizar') { $dados[':quantidade'] = $quantidade; }
                $stmt->execute($dados);
                header('Location: carrinho.php?msg=atualizado'); exit;
            }
            if ($acao === 'finalizar') {
                $pedidoId = finalizarCarrinho($pdo, $usuario['id']);
                header('Location: presentes.php?pedido=' . $pedidoId); exit;
            }
            throw new RuntimeException('Ação inválida.');
        } catch (RuntimeException $e) {
            $erro = $e->getMessage();
        } catch (PDOException $e) {
            $erro = 'Não foi possível atualizar o carrinho. Confira se o banco foi migrado.';
        }
    }
}
$itens = itensCarrinho($pdo, $usuario['id']);
$total = 0;
foreach ($itens as $item) { $total += (float) $item['preco'] * (int) $item['quantidade']; }
function hCarrinho(string $texto): string { return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8'); }

define('MVC_RENDER', true);
require __DIR__ . '/../../views/public/carrinho.php';
