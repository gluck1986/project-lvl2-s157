<?php

namespace GenDiff\CliMiddleware;

use Docopt\Handler;
use function GenDiff\Differ\genDiff as differGenDiff;

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

function genDiff()
{
    list($firstPath, $secondPath, $format) = route();

    return differGenDiff($firstPath, $secondPath, $format);
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
