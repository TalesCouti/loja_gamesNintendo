<?php
require_once __DIR__ . '/../config/autenticacao.php';
exigirAdministrador('../../login.php');

require_once __DIR__ . '/../models/games.php';
require_once __DIR__ . '/../models/categorias.php';
require_once __DIR__ . '/../models/plataformas.php';
require_once __DIR__ . '/../config/imagens.php';
$categorias = (new Categoria($pdo))->listar();
$plataformas = (new Plataforma($pdo))->listar();

$game = new Game();
$dados = null;
$mensagem = '';

if (isset($_GET['id'])) {
    $dados = $game->buscarGamePorId($_GET['id']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!tokenCsrfValido($_POST['csrf_token'] ?? null)) {
            throw new RuntimeException('A sessão do formulário expirou. Tente novamente.');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $dadosAtuais = $game->buscarGamePorId($id);

        if (!$dadosAtuais) {
            throw new RuntimeException('O jogo informado não foi encontrado.');
        }

        $novaImagem = salvarImagemGame($_FILES['imagem'] ?? []);
        $imagemNome = $novaImagem ?? ($dadosAtuais['imagem'] ?? '');

        $editar = new Game([
            'id' => $id,
            'titulo' => $_POST['titulo'] ?? '',
            'descricao' => $_POST['descricao'] ?? '',
            'preco' => $_POST['preco'] ?? '',
            'estoque' => $_POST['estoque'] ?? '',
            'categoria' => $_POST['categoria'] ?? '',
            'plataforma' => $_POST['plataforma'] ?? '',
            'imagem' => $imagemNome,
        ]);

        if (!$editar->editarGame()) {
            throw new RuntimeException('Não foi possível atualizar o jogo.');
        }

        header('Location: ver_games.php?editado=1');
        exit;
    } catch (RuntimeException $e) {
        $mensagem = $e->getMessage();
        $dados = $game->buscarGamePorId((int) ($_POST['id'] ?? 0));
    }
}

define('MVC_RENDER', true);
require __DIR__ . '/../views/templates/editar_games.php';
