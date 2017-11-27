<?php

namespace GenDiff\CliFunctions;

use Docopt\Handler;
use function GenDiff\Differ\genDiff;

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

function runDiffer()
{
    list($firstPath, $secondPath, $format) = route();

    return genDiff($firstPath, $secondPath, $format);
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
        'help' => true,
    ];
}
