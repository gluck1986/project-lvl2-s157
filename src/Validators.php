<?php

namespace GenDiff\Validators;

use GenDiff\GenDiffException;
use function GenDiff\FSFunctions\findNotExistsFiles;
use function GenDiff\FSFunctions\getExt;

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
