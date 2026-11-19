<?php
require_once __DIR__ . '/../config/autenticacao.php';
exigirAdministrador('../../login.php');
require_once __DIR__ . '/../models/pedidos.php';
require_once __DIR__ . '/../models/games.php';
require_once __DIR__ . '/../models/usuarios.php';
$modelo = new Pedido($pdo);
$pedidos = $modelo->listar();
$editar = isset($_GET['editar']) ? $modelo->buscar((int) $_GET['editar']) : null;
$usuarios = (new Usuario())->listarUsuarios();
$games = (new Game())->listarGames();
$mensagem = $_SESSION['pedidos_mensagem'] ?? null;
unset($_SESSION['pedidos_mensagem']);
function hPedido(string $valor): string { return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8'); }

define('MVC_RENDER', true);
require __DIR__ . '/../views/templates/pedidos.php';
