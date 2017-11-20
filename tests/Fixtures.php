<?php

namespace GenDiff\Tests\Fixtures;

function getValidBodyOneLevel()
{
    return [
        json_encode([
            "host" => "hexlet.io",
            "timeout" => 50,
            "proxy" => "123.234.53.22",
        ]),
        json_encode([
            "timeout" => 20,
            "verbose" => true,
            "host" => "hexlet.io"
        ])
    ];
}
