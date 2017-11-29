<?php

namespace GenDiff\Differ;

use GenDiff\GenDiffException;
use const GenDiff\ASTBuilder\FORMAT_PRETTY;
use function GenDiff\ASTBuilder\buildAST;
use function GenDiff\Parser\parse;
use function GenDiff\ResponseBuilder\buildResponse;

function genDiff($fileBefore, $fileAfter, $format = FORMAT_PRETTY)
{
    validateFile($fileBefore);
    validateFile($fileAfter);
    $rawContentBefore = file_get_contents($fileBefore);
    $rawContentAfter = file_get_contents($fileAfter);
    $contentBefore = parse($rawContentBefore, pathinfo($fileBefore, PATHINFO_EXTENSION));
    $contentAfter = parse($rawContentAfter, pathinfo($fileAfter, PATHINFO_EXTENSION));
    $ast = buildAST($contentBefore, $contentAfter);

    return buildResponse($ast, $format);
}

function validateFile($filePath)
{
    if (!file_exists($filePath)) {
        throw new GenDiffException('Файл не найден: "' . $filePath . '"');
    }

    $ext = pathinfo($filePath, PATHINFO_EXTENSION);
    if (mb_strlen($ext) === 0) {
        throw new GenDiffException('У файлов отсутствует расширение');
    }
}
