<?php

const GAME_PLACEHOLDER_IMAGE = 'mario-kart-8-deluxe.webp';

function diretorioImagensGames(): string
{
    return __DIR__ . '/../public/imagens';
}

function imagemGameOuPlaceholder(?string $nomeArquivo): string
{
    $nomeSeguro = basename(trim((string) $nomeArquivo));

    if ($nomeSeguro !== '' && is_file(diretorioImagensGames() . DIRECTORY_SEPARATOR . $nomeSeguro)) {
        return $nomeSeguro;
    }

    return GAME_PLACEHOLDER_IMAGE;
}

function salvarImagemGame(array $arquivo): ?string
{
    $erro = $arquivo['error'] ?? UPLOAD_ERR_NO_FILE;

    if ($erro === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($erro !== UPLOAD_ERR_OK || empty($arquivo['tmp_name'])) {
        throw new RuntimeException('Não foi possível receber a imagem enviada.');
    }

    $tiposPermitidos = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $tipo = $finfo->file($arquivo['tmp_name']);

    if (!isset($tiposPermitidos[$tipo])) {
        throw new RuntimeException('Envie uma imagem JPG, PNG ou WebP.');
    }

    $diretorio = diretorioImagensGames();
    if (!is_dir($diretorio) && !mkdir($diretorio, 0775, true) && !is_dir($diretorio)) {
        throw new RuntimeException('Não foi possível preparar a pasta de imagens.');
    }

    $nomeArquivo = 'game-' . bin2hex(random_bytes(8)) . '.' . $tiposPermitidos[$tipo];
    $destino = $diretorio . DIRECTORY_SEPARATOR . $nomeArquivo;

    if (!move_uploaded_file($arquivo['tmp_name'], $destino)) {
        throw new RuntimeException('Não foi possível salvar a imagem enviada.');
    }

    return $nomeArquivo;
}
