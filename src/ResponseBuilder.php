<?php

namespace GenDiff\ResponseBuilder;

use const GenDiff\ASTDefines\RESPONSE_SPACES_NEXT_LEVEL;
use const GenDiff\ASTDefines\STR_STATUS_ADD;
use const GenDiff\ASTDefines\STR_STATUS_IDENTICAL;
use const GenDiff\ASTDefines\STR_STATUS_REMOVE;

function buildErrResponse(array $errs)
{
    return 'Ошибка: ' . implode(', ', $errs) . PHP_EOL;
}

function generateSpaces($count)
{
    return implode(' ', array_fill(0, $count + 1, ''));
}

function buildResponse(array $results, int $spaces = 0): string
{
    $resultString = implode(
        PHP_EOL,
        array_map(
            function ($itemArr) use ($spaces) {
                return generateSpaces($spaces)
                    . getStatusLabel($itemArr['state'])
                    . '"' . $itemArr['key'] . '": '
                    . (is_array($itemArr['value'])
                        ? buildResponse($itemArr['value'], $spaces + RESPONSE_SPACES_NEXT_LEVEL)
                        : buildResponseValue($itemArr['value']));
            },
            $results
        )
    );

    return '{' . PHP_EOL . $resultString . PHP_EOL . generateSpaces($spaces) . '}';
}

function buildResponseValue($value)
{
    if (is_bool($value)) {
        return ($value ? 'true' : 'false');
    } else {
        return '"' . $value . '"';
    }
}

function getStatusLabel(string $status): string
{
    $statusLabels = [
        STR_STATUS_ADD => '  + ',
        STR_STATUS_REMOVE => '  - ',
        STR_STATUS_IDENTICAL => '    ',
    ];

    return $statusLabels[$status];
}
