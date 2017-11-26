<?php

namespace GenDiff\ResponseBuilder;

use const GenDiff\ASTDefines\KEY_DATA_AFTER;
use const GenDiff\ASTDefines\KEY_DATA_BEFORE;
use const GenDiff\ASTDefines\KEY_KEY;
use const GenDiff\ASTDefines\KEY_STATE;
use const GenDiff\ASTDefines\RESPONSE_SPACES_NEXT_LEVEL;
use const GenDiff\ASTDefines\STATE_ADDED;
use const GenDiff\ASTDefines\STATE_NESTED_AFTER;
use const GenDiff\ASTDefines\STATE_NESTED_BEFORE;
use const GenDiff\ASTDefines\STATE_REMOVED;
use const GenDiff\ASTDefines\STATE_UPDATED;

function generateSpaces($count)
{
    return implode(' ', array_fill(0, $count + 1, ''));
}

function buildResponse(array $results, int $spaces = 0): string
{
    $resultString = implode(
        PHP_EOL,
        array_reduce(
            $results,
            function ($result, $itemArr) use ($spaces) {
                if ($itemArr[KEY_STATE] & STATE_ADDED) {
                    $result[] = generateSpaces($spaces)
                        . getStatusLabel($itemArr[KEY_STATE])
                        . '"' . $itemArr[KEY_KEY] . '": '
                        . buildResponseValue(
                            $itemArr[KEY_DATA_AFTER],
                            $itemArr[KEY_STATE] & STATE_NESTED_AFTER,
                            $spaces + RESPONSE_SPACES_NEXT_LEVEL
                        );
                } elseif ($itemArr[KEY_STATE] & STATE_REMOVED) {
                    $result[] = generateSpaces($spaces)
                        . getStatusLabel($itemArr[KEY_STATE])
                        . '"' . $itemArr[KEY_KEY] . '": '
                        . buildResponseValue(
                            $itemArr[KEY_DATA_BEFORE],
                            $itemArr[KEY_STATE] & STATE_NESTED_BEFORE,
                            $spaces + RESPONSE_SPACES_NEXT_LEVEL
                        );
                } elseif ($itemArr[KEY_STATE] & STATE_UPDATED) {
                    $result[] = generateSpaces($spaces)
                        . getStatusLabel(STATE_REMOVED)
                        . '"' . $itemArr[KEY_KEY] . '": '
                        . buildResponseValue(
                            $itemArr[KEY_DATA_BEFORE],
                            $itemArr[KEY_STATE] & STATE_NESTED_BEFORE,
                            $spaces + RESPONSE_SPACES_NEXT_LEVEL
                        );

                    $result[] = generateSpaces($spaces)
                        . getStatusLabel(STATE_ADDED)
                        . '"' . $itemArr[KEY_KEY] . '": '
                        . buildResponseValue(
                            $itemArr[KEY_DATA_AFTER],
                            $itemArr[KEY_STATE] & STATE_NESTED_AFTER,
                            $spaces + RESPONSE_SPACES_NEXT_LEVEL
                        );
                } else {
                    $result[] = generateSpaces($spaces)
                        . getStatusLabel($itemArr[KEY_STATE])
                        . '"' . $itemArr[KEY_KEY] . '": '
                        . buildResponseValue(
                            $itemArr[KEY_DATA_BEFORE],
                            $itemArr[KEY_STATE] & STATE_NESTED_BEFORE,
                            $spaces + RESPONSE_SPACES_NEXT_LEVEL
                        );
                }

                return $result;
            },
            []
        )
    );

    return '{' . PHP_EOL . $resultString . PHP_EOL . generateSpaces($spaces) . '}';
}

function buildResponseValue($data, $nested, $spaces)
{
    if ($nested) {
        return buildResponse($data, $spaces);
    }
    if (is_bool($data)) {
        return ($data ? 'true' : 'false');
    } else {
        return '"' . $data . '"';
    }
}

function getStatusLabel(int $status): string
{
    if ($status & STATE_ADDED) {
        return '  + ';
    } elseif ($status & STATE_REMOVED) {
        return '  - ';
    } else {
        return '    ';
    }
}
