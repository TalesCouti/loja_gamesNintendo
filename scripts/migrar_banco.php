<?php
// Atualiza um banco criado por versões anteriores sem apagar os dados existentes.
require_once __DIR__ . '/../admin/config/conexao.php';

function existeTabela(PDO $pdo, string $nome): bool
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :nome');
    $stmt->execute([':nome' => $nome]);
    return (int) $stmt->fetchColumn() > 0;
}

function existeColuna(PDO $pdo, string $tabela, string $coluna): bool
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :tabela AND COLUMN_NAME = :coluna');
    $stmt->execute([':tabela' => $tabela, ':coluna' => $coluna]);
    return (int) $stmt->fetchColumn() > 0;
}

function existeReferencia(PDO $pdo, string $coluna): bool
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :tabela AND COLUMN_NAME = :coluna AND REFERENCED_TABLE_NAME IS NOT NULL');
    $stmt->execute([':tabela' => 'games', ':coluna' => $coluna]);
    return (int) $stmt->fetchColumn() > 0;
}

if (!existeTabela($pdo, 'usuarios') || !existeTabela($pdo, 'games')) {
    exit("Importe banco de dados/schema.sql para uma instalação nova.\n");
}

if (!existeColuna($pdo, 'usuarios', 'telefone')) {
    $pdo->exec('ALTER TABLE usuarios ADD COLUMN telefone VARCHAR(20) NULL AFTER email');
}
if (!existeTabela($pdo, 'categorias')) {
    $pdo->exec("CREATE TABLE categorias (id INT AUTO_INCREMENT PRIMARY KEY, nome VARCHAR(50) NOT NULL UNIQUE, descricao VARCHAR(255) NOT NULL DEFAULT '')");
}
if (!existeTabela($pdo, 'plataformas')) {
    $pdo->exec("CREATE TABLE plataformas (id INT AUTO_INCREMENT PRIMARY KEY, nome VARCHAR(50) NOT NULL UNIQUE, descricao VARCHAR(255) NOT NULL DEFAULT '')");
}
if (!existeColuna($pdo, 'games', 'plataforma')) {
    $pdo->exec('ALTER TABLE games ADD COLUMN plataforma VARCHAR(50) NULL AFTER categoria');
}
$pdo->exec("INSERT IGNORE INTO categorias (nome) SELECT DISTINCT TRIM(categoria) FROM games WHERE categoria IS NOT NULL AND TRIM(categoria) <> ''");
$pdo->exec("INSERT IGNORE INTO plataformas (nome) SELECT DISTINCT TRIM(plataforma) FROM games WHERE plataforma IS NOT NULL AND TRIM(plataforma) <> ''");
if (!existeReferencia($pdo, 'categoria')) {
    $pdo->exec('ALTER TABLE games ADD CONSTRAINT fk_games_categoria_nome FOREIGN KEY (categoria) REFERENCES categorias(nome) ON DELETE SET NULL ON UPDATE CASCADE');
}
if (!existeReferencia($pdo, 'plataforma')) {
    $pdo->exec('ALTER TABLE games ADD CONSTRAINT fk_games_plataforma_nome FOREIGN KEY (plataforma) REFERENCES plataformas(nome) ON DELETE SET NULL ON UPDATE CASCADE');
}

$sql = file_get_contents(__DIR__ . '/../banco de dados/migracao_social.sql');
foreach (explode(';', $sql) as $comando) {
    if (trim($comando) !== '') {
        $pdo->exec($comando);
    }
}
if (!existeColuna($pdo, 'pedidos', 'status')) {
    $pdo->exec("ALTER TABLE pedidos ADD COLUMN status ENUM('confirmado', 'cancelado') NOT NULL DEFAULT 'confirmado'");
}
if (!existeColuna($pdo, 'pedidos', 'observacao')) {
    $pdo->exec("ALTER TABLE pedidos ADD COLUMN observacao VARCHAR(255) NOT NULL DEFAULT ''");
}
echo "Banco atualizado. Dados existentes preservados.\n";
