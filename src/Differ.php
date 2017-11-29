<?php

namespace GenDiff\Differ;

use GenDiff\GenDiffException;
use const GenDiff\ASTBuilder\FORMAT_PRETTY;
use function GenDiff\ASTBuilder\buildAST;
use function GenDiff\Parser\parse;
use function GenDiff\ResponseBuilder\buildResponse;

const NEED_FILE_NAME_PARTS = 2;

function genDiff($file1, $file2, $format = FORMAT_PRETTY)
{
    validateFile($file1);
    validateFile($file2);
    $contentBefore = parse(file_get_contents($file1), getExt($file1));
    $contentAfter = parse(file_get_contents($file2), getExt($file2));
    $ast = buildAST($contentBefore, $contentAfter);

    return buildResponse($ast, $format);
}

function validateFile($filePath)
{
    if (!file_exists($filePath)) {
        throw new GenDiffException('Файл не найден: "' . $filePath . '"');
    }

    $ext = getExt($filePath);
    if (mb_strlen($ext) === 0) {
        throw new GenDiffException('У файлов отсутствует расширение');
    }
}

function getExt(string $path): string
{
    $fileNameParts = explode('.', basename($path));
    if (count($fileNameParts) === NEED_FILE_NAME_PARTS) {
        $lastPart = $fileNameParts[NEED_FILE_NAME_PARTS - 1];

        return mb_strtolower(trim($lastPart));
    }

    return '';
}
