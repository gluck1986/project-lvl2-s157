<?php

namespace GenDiff\Route;

use Docopt\Handler;

function route($argv)
{
    $handler = new Handler(getParams());

    return $handler->handle(getDoc(), $argv);
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
