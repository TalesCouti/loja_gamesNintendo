<?php
require_once __DIR__ . '/../config/autenticacao.php';
exigirAdministrador('../../login.php');

require_once __DIR__ . '/../models/usuarios.php';

$usuarioModel = new Usuario();
$usuarios = $usuarioModel->listarUsuarios();
$mensagemSessao = $_SESSION['admin_mensagem'] ?? null;
unset($_SESSION['admin_mensagem']);

function hUsuario(string $valor): string
{
    return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
}

define('MVC_RENDER', true);
require __DIR__ . '/../views/templates/ver_usuarios.php';
