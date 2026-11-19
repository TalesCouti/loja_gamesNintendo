<?php
require_once __DIR__ . '/../config/autenticacao.php';
exigirAdministrador('../../login.php');
require_once __DIR__ . '/../models/categorias.php';
require_once __DIR__ . '/../models/plataformas.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método não permitido.');
}
$tipo = (string) ($_POST['tipo'] ?? '');
if (!in_array($tipo, ['categorias', 'plataformas'], true)) {
    http_response_code(400);
    exit('Entidade inválida.');
}
$voltar = '../views/gerenciar_taxonomia.php?tipo=' . $tipo;
try {
    if (!tokenCsrfValido($_POST['csrf_token'] ?? null)) {
        throw new RuntimeException('Sessão expirada. Atualize a página.');
    }
    $modelo = $tipo === 'categorias' ? new Categoria($pdo) : new Plataforma($pdo);
    $acao = (string) ($_POST['acao'] ?? '');
    $id = (int) ($_POST['id'] ?? 0);
    if ($acao === 'criar') {
        $modelo->criar((string) ($_POST['nome'] ?? ''), (string) ($_POST['descricao'] ?? ''));
    } elseif ($acao === 'atualizar') {
        $modelo->atualizar($id, (string) ($_POST['nome'] ?? ''), (string) ($_POST['descricao'] ?? ''));
    } elseif ($acao === 'excluir') {
        if (!$modelo->excluir($id)) { throw new RuntimeException('Registro não encontrado.'); }
    } else {
        throw new RuntimeException('Ação inválida.');
    }
    $_SESSION['taxonomia_mensagem'] = ['erro' => false, 'texto' => 'Registro atualizado com sucesso.'];
} catch (PDOException $e) {
    $_SESSION['taxonomia_mensagem'] = ['erro' => true, 'texto' => $e->getCode() === '23000' ? 'Este nome já existe ou há jogos vinculados a ele.' : 'Não foi possível salvar o registro.'];
} catch (RuntimeException $e) {
    $_SESSION['taxonomia_mensagem'] = ['erro' => true, 'texto' => $e->getMessage()];
}
header('Location: ' . $voltar);
exit;
