<?php

namespace GenDiff\ResponseBuilder;

use GenDiff\GenDiffException;
use const GenDiff\ASTDefines\FORMAT_PLAIN;
use const GenDiff\ASTDefines\FORMAT_PRETTY;
use const GenDiff\ASTDefines\KEY_DATA_AFTER;
use const GenDiff\ASTDefines\KEY_DATA_BEFORE;
use const GenDiff\ASTDefines\KEY_KEY;
use const GenDiff\ASTDefines\KEY_LABEL;
use const GenDiff\ASTDefines\KEY_STATE;
use const GenDiff\ASTDefines\RESPONSE_SPACES_NEXT_LEVEL;
use const GenDiff\ASTDefines\STATE_ADDED;
use const GenDiff\ASTDefines\STATE_IDENTICAL;
use const GenDiff\ASTDefines\STATE_NESTED_AFTER;
use const GenDiff\ASTDefines\STATE_NESTED_BEFORE;
use const GenDiff\ASTDefines\STATE_REMOVED;
use const GenDiff\ASTDefines\STATE_UPDATED;

function generateSpaces($count)
{
    return implode(' ', array_fill(0, $count + 1, ''));
}


function buildResponse(array $ast, $format = FORMAT_PRETTY)
{
    if ($format === FORMAT_PRETTY) {
        return buildPretty($ast);
    } elseif ($format === FORMAT_PLAIN) {
        return buildPlain($ast);
    }
    throw new GenDiffException('Не известный формат: ' . $format);
}

function buildPlain(array $ast)
{
    return
        implode(
            PHP_EOL,
            array_map(
                function ($val) {
                    return 'Property \'' . $val[KEY_LABEL] . '\' ' . $val[KEY_STATE];
                },
                buildPlainArr($ast)
            )
        ) . PHP_EOL;
}

function buildPlainArr(array $ast)
{
    return array_reduce(
        $ast,
        function ($result, $node) {
            $label = $node[KEY_KEY];
            if ($node[KEY_STATE] & STATE_REMOVED) {
                $result[] = [KEY_LABEL => $label, KEY_STATE => 'was removed'];
            }
            if ($node[KEY_STATE] & STATE_ADDED) {
                $label = $node[KEY_KEY];
                if ($node[KEY_STATE] & STATE_NESTED_AFTER) {
                    $result[] = [
                        KEY_LABEL => $label,
                        KEY_STATE => 'was added with value: \'complex value\''
                    ];
                } else {
                    $result[] = [
                        KEY_LABEL => $label,
                        KEY_STATE => 'was added with value: \'' . $node[KEY_DATA_AFTER] . '\''
                    ];
                }
            }
            if ($node[KEY_STATE] & STATE_UPDATED) {
                if ($node[KEY_STATE] & STATE_NESTED_BEFORE) {
                    $result[] = [
                        KEY_LABEL => $label,
                        KEY_STATE => 'was changed. From: \'complex value\' to \'' . $node[KEY_DATA_AFTER] . '\''
                    ];
                } elseif ($node[KEY_STATE] & STATE_NESTED_AFTER) {
                    $result[] = [
                        KEY_LABEL => $label,
                        KEY_STATE => 'was changed. From: \'' . $node[KEY_DATA_BEFORE] . '\''
                            . ' to \'complex value\''
                    ];
                } else {
                    $result[] = [
                        KEY_LABEL => $label,
                        KEY_STATE => 'was changed. From: \''
                            . $node[KEY_DATA_BEFORE] . '\''
                            . ' to \'' . $node[KEY_DATA_AFTER] . '\''
                    ];
                }
            }
            if ($node[KEY_STATE] & STATE_IDENTICAL
                && $node[KEY_STATE] & STATE_NESTED_BEFORE
                && $node[KEY_STATE] & STATE_NESTED_AFTER) {
                $nested = buildPlainArr($node[KEY_DATA_BEFORE]);
                if (count($nested) > 0) {
                    $result = array_merge(
                        $result,
                        array_map(
                            function ($val) use ($label) {
                                $val[KEY_LABEL] = $label . '.' . $val[KEY_LABEL];

                                return $val;
                            },
                            $nested
                        )
                    );
                }
            }

            return $result;
        },
        []
    );
}


function buildPretty(array $ast, int $spaces = 0): string
{
    $resultString = implode(
        PHP_EOL,
        array_reduce(
            $ast,
            function ($result, $node) use ($spaces) {
                if ($node[KEY_STATE] & STATE_ADDED) {
                    $result[] = generateSpaces($spaces)
                        . getPrettyStatusLabel($node[KEY_STATE])
                        . '"' . $node[KEY_KEY] . '": '
                        . buildPrettyValue(
                            $node[KEY_DATA_AFTER],
                            $node[KEY_STATE] & STATE_NESTED_AFTER,
                            $spaces + RESPONSE_SPACES_NEXT_LEVEL
                        );
                } elseif ($node[KEY_STATE] & STATE_REMOVED) {
                    $result[] = generateSpaces($spaces)
                        . getPrettyStatusLabel($node[KEY_STATE])
                        . '"' . $node[KEY_KEY] . '": '
                        . buildPrettyValue(
                            $node[KEY_DATA_BEFORE],
                            $node[KEY_STATE] & STATE_NESTED_BEFORE,
                            $spaces + RESPONSE_SPACES_NEXT_LEVEL
                        );
                } elseif ($node[KEY_STATE] & STATE_UPDATED) {
                    $result[] = generateSpaces($spaces)
                        . getPrettyStatusLabel(STATE_REMOVED)
                        . '"' . $node[KEY_KEY] . '": '
                        . buildPrettyValue(
                            $node[KEY_DATA_BEFORE],
                            $node[KEY_STATE] & STATE_NESTED_BEFORE,
                            $spaces + RESPONSE_SPACES_NEXT_LEVEL
                        );

                    $result[] = generateSpaces($spaces)
                        . getPrettyStatusLabel(STATE_ADDED)
                        . '"' . $node[KEY_KEY] . '": '
                        . buildPrettyValue(
                            $node[KEY_DATA_AFTER],
                            $node[KEY_STATE] & STATE_NESTED_AFTER,
                            $spaces + RESPONSE_SPACES_NEXT_LEVEL
                        );
                } else {
                    $result[] = generateSpaces($spaces)
                        . getPrettyStatusLabel($node[KEY_STATE])
                        . '"' . $node[KEY_KEY] . '": '
                        . buildPrettyValue(
                            $node[KEY_DATA_BEFORE],
                            $node[KEY_STATE] & STATE_NESTED_BEFORE,
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

function buildPrettyValue($data, $nested, $spaces)
{
    if ($nested) {
        return buildPretty($data, $spaces);
    }
    if (is_bool($data)) {
        return ($data ? 'true' : 'false');
    } else {
        return '"' . $data . '"';
    }
}

function getPrettyStatusLabel(int $status): string
{
    if ($status & STATE_ADDED) {
        return '  + ';
    } elseif ($status & STATE_REMOVED) {
        return '  - ';
    } else {
        return '    ';
    }
}
