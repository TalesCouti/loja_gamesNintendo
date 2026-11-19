<?php
require_once __DIR__ . '/../../config/autenticacao.php';

encerrarSessao();

header('Location: login.php?logout=1');
exit;
