<?php

namespace GenDiff\Cli;

function run()
{
    $doc = <<<DOC
Generate diff

Usage:
  gendiff (-h|--help)
  gendiff [--format <fmt>] <firstFile> <secondFile>

Options:
  -h --help                     Show this screen
  --format <fmt>                Report format [default: pretty]
DOC;

    $args = \Docopt::handle($doc, array('version'=>'Generate diff 0.0.1a'));
    foreach ($args as $k => $v) {
        echo $k.': '.json_encode($v) . PHP_EOL;
    }
}
