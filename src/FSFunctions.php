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

function getFilesContent(string $path1, string $path2)
{
    return [file_get_contents($path1), file_get_contents($path2)];
}
