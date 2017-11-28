<?php

namespace GenDiff\Cli;

use function GenDiff\CliMiddleware\genDiff;

function run()
{
    echo genDiff();
}
