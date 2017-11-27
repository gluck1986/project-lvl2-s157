<?php

namespace GenDiff\Parser;

use GenDiff\GenDiffException;
use Symfony\Component\Yaml\Yaml;

function parse($src, $ext)
{
    $actions = getArrayParserByExt();
    if (!array_key_exists($ext, $actions)) {
        throw new GenDiffException('Не известный формат файлов');
    }

    return getArrayParserByExt()[$ext]($src);
}

function getArrayParserByExt()
{
    return [
        'json' => function ($src) {
            return json_decode($src, true);
        },
        'yml' => function ($src) {
            return Yaml::parse($src, true);
        },
    ];
}
