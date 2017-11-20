<?php

namespace GenDiff\Cli;

use function GenDiff\Differ\genDiff;

function run()
{
    $argv = $_SERVER['argv'];
    $args = route($argv);
    genDiff(1, 2);
}
