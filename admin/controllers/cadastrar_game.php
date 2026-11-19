<?php
require_once __DIR__ . '/../config/autenticacao.php';
exigirAdministrador('../../login.php');

require_once __DIR__ . '/../models/games.php';
require_once __DIR__ . '/../models/categorias.php';
require_once __DIR__ . '/../models/plataformas.php';
require_once __DIR__ . '/../config/imagens.php';
$categorias = (new Categoria($pdo))->listar();
$plataformas = (new Plataforma($pdo))->listar();

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!tokenCsrfValido($_POST['csrf_token'] ?? null)) {
            throw new RuntimeException('A sessão do formulário expirou. Tente novamente.');
        }

        $imagemNome = salvarImagemGame($_FILES['imagem'] ?? []);
        $novo = new Game([
            'titulo' => $_POST['titulo'] ?? '',
            'descricao' => $_POST['descricao'] ?? '',
            'preco' => $_POST['preco'] ?? '',
            'estoque' => $_POST['estoque'] ?? '',
            'categoria' => $_POST['categoria'] ?? '',
            'plataforma' => $_POST['plataforma'] ?? '',
            'imagem' => $imagemNome ?? '',
        ]);

        if ($novo->cadastrarGame()) {
            $mensagem = 'sucesso';
        } else {
            $mensagem = 'Erro ao cadastrar no banco de dados.';
        }
    } catch (RuntimeException $e) {
        $mensagem = $e->getMessage();
    }
}

define('MVC_RENDER', true);
require __DIR__ . '/../views/templates/cadastrar_game.php';
