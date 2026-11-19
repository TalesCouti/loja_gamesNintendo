<?php
require_once __DIR__ . '/../config/autenticacao.php';
require_once __DIR__ . '/../models/usuarios.php';

iniciarSessaoSegura();
if (usuarioAutenticado()) {
    header('Location: index.php');
    exit;
}

$erroCadastro = '';
$dadosCadastro = ['nome' => '', 'email' => '', 'telefone' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($dadosCadastro as $campo => $_) {
        $dadosCadastro[$campo] = trim((string) ($_POST[$campo] ?? ''));
    }
    try {
        if (!tokenCsrfValido($_POST['csrf_token'] ?? null)) {
            throw new RuntimeException('Sessão expirada. Atualize a página e tente novamente.');
        }
        if ($dadosCadastro['telefone'] === '') {
            throw new RuntimeException('Informe seu telefone com DDD.');
        }
        if ((string) ($_POST['senha'] ?? '') !== (string) ($_POST['confirmar_senha'] ?? '')) {
            throw new RuntimeException('As senhas não conferem.');
        }
        (new Usuario())->cadastrarUsuario([
            'nome' => $dadosCadastro['nome'],
            'email' => $dadosCadastro['email'],
            'telefone' => $dadosCadastro['telefone'],
            'senha' => (string) ($_POST['senha'] ?? ''),
            'tipo' => 'user',
        ]);
        header('Location: login.php?cadastro=1');
        exit;
    } catch (RuntimeException $e) {
        $erroCadastro = $e->getMessage();
    }
}
