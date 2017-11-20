<?php

namespace GenDiff\Cli;

use function GenDiff\CliFunctions\getFiles;
use function GenDiff\CliFunctions\route;
use function GenDiff\Differ\genDiff;

function run()
{
    list($firstPath,$secondPath, $format) = route();
    list($firstFile, $secondFile) = getFiles($firstPath, $secondPath);
    if (is_null($firstFile)) {
        echo 'Файл не найден ' . $firstPath . PHP_EOL;
    }
    if (is_null($secondFile)) {
        echo 'Файл не найден ' . $secondPath . PHP_EOL;
    }
    if (is_null($firstFile) || is_null($secondFile)) {
        return;
    }
    echo genDiff($firstFile, $secondFile);
}
