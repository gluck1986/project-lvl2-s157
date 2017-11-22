<?php

namespace GenDiff\FSFunctions;

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
