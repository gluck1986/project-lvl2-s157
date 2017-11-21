<?php

namespace GenDiff\Differ;

use Symfony\Component\Yaml\Yaml;
use function GenDiff\ASTBuilder\build;
use function GenDiff\FSFunctions\getExt;
use function GenDiff\FSFunctions\getFilesContent;
use function GenDiff\ResponseBuilder\buildErrResponse;
use function GenDiff\ResponseBuilder\buildResponse;
use function GenDiff\Validators\validateFiles;

function diffJson($srcFirst, $srcSecond)
{
    return build(
        json_decode($srcFirst, true),
        json_decode($srcSecond, true)
    );
}

function diffYaml($srcFirst, $srcSecond)
{
    return build(
        Yaml::parse($srcFirst),
        Yaml::parse($srcSecond)
    );
}

function genDiff($pathFirst, $pathSecond)
{
    $errs = validateFiles($pathFirst, $pathSecond);
    if (count($errs) > 0) {
        return buildErrResponse($errs);
    }
    $differ = getDiffer(getExt($pathFirst));
    $filesContent = getFilesContent($pathFirst, $pathSecond);

    return buildResponse($differ($filesContent[0], $filesContent[1]));
}


function getDiffer($ext): string
{
    return __NAMESPACE__ . '\\' . getExtToActionArray()[$ext];
}

function getExtToActionArray(): array
{
    return [
        'json' => 'diffJson',
        'yml' => 'diffYaml'
    ];
}
