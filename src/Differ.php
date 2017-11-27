<?php

namespace GenDiff\Differ;

use const GenDiff\ASTDefines\FORMAT_PRETTY;
use function GenDiff\ASTBuilder\build;
use function GenDiff\FSFunctions\getExt;
use function GenDiff\FSFunctions\getFilesContent;
use function GenDiff\Parser\parse;
use function GenDiff\ResponseBuilder\buildResponse;
use function GenDiff\Validators\validateFiles;

function genDiff($file1, $file2, $format = FORMAT_PRETTY)
{
    validateFiles([$file1, $file2]);
    list($dataBefore, $dataAfter) = getFilesContent([$file1, $file2]);
    $contentBefore = parse($dataBefore, getExt($file1));
    $contentAfter = parse($dataAfter, getExt($file2));
    $ast = build($contentBefore, $contentAfter);

    return buildResponse($ast, $format);
}
