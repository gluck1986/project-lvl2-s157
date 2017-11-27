<?php

namespace GenDiff\Differ;

use GenDiff\GenDiffException;
use const GenDiff\ASTDefines\FORMAT_PRETTY;
use function GenDiff\ASTBuilder\build;
use function GenDiff\Parser\parse;
use function GenDiff\ResponseBuilder\buildResponse;

function genDiff($file1, $file2, $format = FORMAT_PRETTY)
{
    validateFiles([$file1, $file2]);
    list($dataBefore, $dataAfter) = getFilesContent([$file1, $file2]);
    $contentBefore = parse($dataBefore, getExt($file1));
    $contentAfter = parse($dataAfter, getExt($file2));
    $ast = build($contentBefore, $contentAfter);

    return buildResponse($ast, $format);
}

function isEqualExt(array $files): bool
{
    return array_reduce(
        $files,
        function ($acc, $filePath) {
            if (is_null($acc['last'])) {
                $acc['last'] = getExt($filePath);
            } elseif ($acc['result'] === true
                && $acc['last'] !== getExt($filePath)
            ) {
                $acc['result'] = false;
            }

            return $acc;
        },
        ['last' => null, 'result' => true]
    )['result'];
}

function validateFiles($files): array
{
    $notFindPaths = findNotExistsFiles($files);
    if (count($notFindPaths) > 0) {
        $errs = array_map(
            function ($path) {
                return 'Файл не найден: "' . $path . '"';
            },
            $notFindPaths
        );

        throw new GenDiffException(implode(', ', $errs));
    }
    if (!isEqualExt($files)) {
        throw new GenDiffException('Расширения файлов не совпадают');
    }
    $ext = getExt(reset($files));
    if (mb_strlen($ext) === 0) {
        throw new GenDiffException('У файлов отсутствует расширение');
    }

    return [];
}

function getExt(string $path): string
{
    $baseExt = explode('.', basename($path));
    if (count($baseExt) === 2) {
        return mb_strtolower(trim(array_pop($baseExt)));
    }

    return '';
}

function findNotExistsFiles(array $filePaths): array
{
    return array_filter($filePaths, function ($path) {
        return !file_exists($path);
    });
}

function getFilesContent(array $files): array
{
    return array_map(
        function ($filePath) {
            return file_get_contents($filePath);
        },
        $files
    );
}
