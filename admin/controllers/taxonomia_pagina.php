<?php
require_once __DIR__ . '/../config/autenticacao.php';
exigirAdministrador('../../login.php');
require_once __DIR__ . '/../models/categorias.php';
require_once __DIR__ . '/../models/plataformas.php';
$tipo = (string) ($_GET['tipo'] ?? 'categorias');
if (!in_array($tipo, ['categorias', 'plataformas'], true)) { http_response_code(404); exit('Entidade não encontrada.'); }
$modelo = $tipo === 'categorias' ? new Categoria($pdo) : new Plataforma($pdo);
$titulo = $tipo === 'categorias' ? 'Categorias' : 'Plataformas';
$registros = $modelo->listar();
$editar = isset($_GET['editar']) ? $modelo->buscar((int) $_GET['editar']) : null;
$mensagem = $_SESSION['taxonomia_mensagem'] ?? null;
unset($_SESSION['taxonomia_mensagem']);
function hTax(string $valor): string { return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8'); }

define('MVC_RENDER', true);
require __DIR__ . '/../views/templates/gerenciar_taxonomia.php';
