<?php
require_once __DIR__ . '/taxonomia.php';

class Categoria extends Taxonomia
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, 'categorias');
    }
}
