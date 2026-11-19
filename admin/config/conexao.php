<?php

$local = is_file(__DIR__ . '/local.php') ? require __DIR__ . '/local.php' : [];
$host = getenv('DB_HOST') ?: 'localhost';
$database = getenv('DB_NAME') ?: 'loja_games';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS');
if ($password === false) {
    $password = (string) ($local['password'] ?? '');
}

try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$database};charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    error_log('Falha na conexão com o banco: ' . $e->getMessage());
    http_response_code(503);
    exit('Não foi possível conectar ao banco de dados.');
}
