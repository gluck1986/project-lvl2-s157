<?php

namespace GenDiff\Application;

use function GenDiff\Route\route;

function run()
{
    $argv = $_SERVER['argv'];
    $args = route($argv);
}
