<?php

namespace GenDiff\CliFunctions;

use Docopt\Handler;

function route()
{
    $handler = new Handler(getParams());
    $handle = $handler->handle(getDoc());

    return [
        $handle->args['<firstFile>'],
        $handle->args['<secondFile>'],
        $handle->args['--format']
    ];
}

function getFiles($path1, $path2)
{
    $str1 = null;
    $str2 = null;
    if (file_exists($path1)) {
        $str1  = file_get_contents($path1);
    }
    if (file_exists($path2)) {
        $str2  = file_get_contents($path2);
    }

    return [$str1, $str2];
}


function getDoc(): string
{
    return <<<DOC
Generate diff

Usage:
  gendiff (-h|--help)
  gendiff [--format <fmt>] <firstFile> <secondFile>

Options:
  -h --help                     Show this screen
  --format <fmt>                Report format [default: pretty]
DOC;
}

function getParams()
{
     return [
         'help'=>true,
         ];
}
