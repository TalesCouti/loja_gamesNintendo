<?php
require_once __DIR__ . '/../config/autenticacao.php';
exigirAdministrador('../../login.php');
require_once __DIR__ . '/../models/pedidos.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Método não permitido.'); }
try {
    if (!tokenCsrfValido($_POST['csrf_token'] ?? null)) { throw new RuntimeException('Sessão expirada. Atualize a página.'); }
    $modelo = new Pedido($pdo);
    $acao = (string) ($_POST['acao'] ?? '');
    if ($acao === 'criar') {
        $modelo->criar((int) ($_POST['usuario_id'] ?? 0), (int) ($_POST['game_id'] ?? 0), (int) ($_POST['quantidade'] ?? 0), (string) ($_POST['observacao'] ?? ''));
    } elseif ($acao === 'atualizar') {
        $modelo->atualizar((int) ($_POST['id'] ?? 0), (string) ($_POST['status'] ?? ''), (string) ($_POST['observacao'] ?? ''));
    } elseif ($acao === 'excluir') {
        $modelo->excluir((int) ($_POST['id'] ?? 0));
    } else {
        throw new RuntimeException('Ação inválida.');
    }
    $_SESSION['pedidos_mensagem'] = ['erro' => false, 'texto' => 'Pedido atualizado com sucesso.'];
} catch (RuntimeException $e) {
    $_SESSION['pedidos_mensagem'] = ['erro' => true, 'texto' => $e->getMessage()];
} catch (PDOException $e) {
    $_SESSION['pedidos_mensagem'] = ['erro' => true, 'texto' => 'Não foi possível atualizar o pedido.'];
}
header('Location: ../views/pedidos.php');
exit;
