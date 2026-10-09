<?php
require_once __DIR__ . '/../config/autenticacao.php';
exigirAdministrador('../../login.php');

require_once __DIR__ . '/../models/usuarios.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Método não permitido.');
}

try {
    if (!tokenCsrfValido($_POST['csrf_token'] ?? null)) {
        throw new RuntimeException('A sessão da ação expirou. Tente novamente.');
    }

    $usuarioModel = new Usuario();
    $usuarioModel->deletarUsuario(
        (int) ($_POST['id'] ?? 0),
        (int) $_SESSION['usuario_id']
    );

    header('Location: ../views/ver_usuarios.php?deletado=1');
    exit;
} catch (RuntimeException $e) {
    $_SESSION['admin_mensagem'] = [
        'tipo' => 'erro',
        'texto' => $e->getMessage(),
    ];
    header('Location: ../views/ver_usuarios.php');
    exit;
}
