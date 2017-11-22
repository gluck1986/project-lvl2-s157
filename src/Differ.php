<?php

namespace GenDiff\Differ;

use GenDiff\GenDiffException;
use Symfony\Component\Yaml\Yaml;
use function GenDiff\ASTBuilder\build;
use function GenDiff\FSFunctions\getExt;
use function GenDiff\FSFunctions\getFilesContent;
use function GenDiff\ResponseBuilder\buildResponse;
use function GenDiff\Validators\validateFiles;

function genDiff($file1, $file2)
{
    validateFiles([$file1, $file2]);
    list($content1, $content2) = parse(
        getFilesContent([$file1, $file2]),
        getExt($file1)
    );

    return buildResponse(build($content1, $content2));
}

function parse(array $srcs, $ext)
{
    $actions = getArrayParserByExt();
    if (!array_key_exists($ext, $actions)) {
        throw new GenDiffException('Не известный формат файлов');
    }

    return getArrayParserByExt()[$ext]($srcs);
}

function getArrayParserByExt(): array
{
    return [
        'json' => function ($srcs) {
            return array_map(
                function ($src) {
                    return json_decode($src, true);
                },
                $srcs
            );
        },
        'yml' => function ($srcs) {
            return array_map(
                function ($src) {
                    return Yaml::parse($src, true);
                },
                $srcs
            );
        },
    ];
}
