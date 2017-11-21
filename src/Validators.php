<?php

namespace GenDiff\Validators;

use function GenDiff\Differ\getExtToActionArray;
use function GenDiff\FSFunctions\findNotExistsFiles;
use function GenDiff\FSFunctions\getExt;

function isEqualExt(string $path1, string $path2): bool
{
    return getExt($path1) === getExt($path2);
}

function validateFiles($filePath1, $filePath2): array
{
    $notFindPaths = findNotExistsFiles([$filePath1, $filePath2]);
    if (count($notFindPaths) > 0) {
        return array_map(
            function ($path) {
                return 'Файл не найден: "' . $path . '"';
            },
            $notFindPaths
        );
    }
    if (!isEqualExt($filePath1, $filePath2)) {
        return ['Расширения файлов не совпадают'];
    }
    $ext = getExt($filePath1);

    if (mb_strlen($ext) === 0) {
        return ['У файлов отсутствует расширение'];
    }
    if (!array_key_exists($ext, getExtToActionArray())) {
        return ['Не известный формат файлов: ' . $ext];
    }

    return [];
}
