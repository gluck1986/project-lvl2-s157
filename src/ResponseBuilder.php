<?php

namespace GenDiff\ResponseBuilder;

use GenDiff\GenDiffException;
use const GenDiff\ASTBuilder\FORMAT_JSON;
use const GenDiff\ASTBuilder\FORMAT_PLAIN;
use const GenDiff\ASTBuilder\FORMAT_PRETTY;
use const GenDiff\ASTBuilder\KEY_CHILDREN;
use const GenDiff\ASTBuilder\KEY_KEY;
use const GenDiff\ASTBuilder\KEY_TYPE;
use const GenDiff\ASTBuilder\KEY_VALUE_AFTER;
use const GenDiff\ASTBuilder\KEY_VALUE_BEFORE;
use const GenDiff\ASTBuilder\TYPE_ADDED;
use const GenDiff\ASTBuilder\TYPE_IDENTICAL;
use const GenDiff\ASTBuilder\TYPE_REMOVED;
use const GenDiff\ASTBuilder\TYPE_UPDATED;

const KEY_RESPONSE_LABEL = 'label';
const KEY_RESPONSE_STATE = 'state';
const RESPONSE_SPACES_NEXT_LEVEL = 4;

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
    } elseif ($format === FORMAT_JSON) {
        return buildJSON($ast);
    }
    throw new GenDiffException('Не известный формат: ' . $format);
}

function buildJSON(array $ast)
{
    return json_encode($ast);
}

function buildPlain(array $ast)
{
    return
        implode(
            PHP_EOL,
            array_map(
                function ($val) {
                    return 'Property \'' . $val[KEY_RESPONSE_LABEL] . '\' ' . $val[KEY_RESPONSE_STATE];
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
            if ($node[KEY_TYPE] === TYPE_REMOVED) {
                $result[] = [KEY_RESPONSE_LABEL => $label, KEY_RESPONSE_STATE => 'was removed'];
            }
            if ($node[KEY_TYPE] === TYPE_ADDED) {
                $label = $node[KEY_KEY];
                if ($node[KEY_CHILDREN]) {
                    $result[] = [
                        KEY_RESPONSE_LABEL => $label,
                        KEY_RESPONSE_STATE => 'was added with value: \'complex value\''
                    ];
                } else {
                    $result[] = [
                        KEY_RESPONSE_LABEL => $label,
                        KEY_RESPONSE_STATE => 'was added with value: \'' . $node[KEY_VALUE_AFTER] . '\''
                    ];
                }
            }
            if ($node[KEY_TYPE] === TYPE_UPDATED) {
                if ($node[KEY_CHILDREN] && is_null($node[KEY_VALUE_BEFORE])) {
                    $result[] = [
                        KEY_RESPONSE_LABEL => $label,
                        KEY_RESPONSE_STATE => 'was changed. From: \'complex value\' to \''
                            . $node[KEY_VALUE_AFTER] . '\''
                    ];
                } elseif ($node[KEY_CHILDREN] && is_null($node[KEY_VALUE_AFTER])) {
                    $result[] = [
                        KEY_RESPONSE_LABEL => $label,
                        KEY_RESPONSE_STATE => 'was changed. From: \'' . $node[KEY_VALUE_BEFORE] . '\''
                            . ' to \'complex value\''
                    ];
                } else {
                    $result[] = [
                        KEY_RESPONSE_LABEL => $label,
                        KEY_RESPONSE_STATE => 'was changed. From: \''
                            . $node[KEY_VALUE_BEFORE] . '\''
                            . ' to \'' . $node[KEY_VALUE_AFTER] . '\''
                    ];
                }
            }
            if ($node[KEY_TYPE] === TYPE_IDENTICAL
                && $node[KEY_CHILDREN]
            ) {
                $nested = buildPlainArr($node[KEY_CHILDREN]);
                if (count($nested) > 0) {
                    $result = array_merge(
                        $result,
                        array_map(
                            function ($val) use ($label) {
                                $val[KEY_RESPONSE_LABEL] = $label . '.' . $val[KEY_RESPONSE_LABEL];

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
                if ($node[KEY_TYPE] === TYPE_ADDED) {
                    $result[] = generateSpaces($spaces)
                        . getPrettyStatusLabel($node[KEY_TYPE])
                        . '"' . $node[KEY_KEY] . '": '
                        . buildPrettyValue(
                            $node[KEY_VALUE_AFTER] ?? $node[KEY_CHILDREN],
                            $spaces + RESPONSE_SPACES_NEXT_LEVEL
                        );
                } elseif ($node[KEY_TYPE] === TYPE_REMOVED) {
                    $result[] = generateSpaces($spaces)
                        . getPrettyStatusLabel($node[KEY_TYPE])
                        . '"' . $node[KEY_KEY] . '": '
                        . buildPrettyValue(
                            $node[KEY_VALUE_BEFORE] ?? $node[KEY_CHILDREN],
                            $spaces + RESPONSE_SPACES_NEXT_LEVEL
                        );
                } elseif ($node[KEY_TYPE] === TYPE_UPDATED) {
                    $result[] = generateSpaces($spaces)
                        . getPrettyStatusLabel(TYPE_REMOVED)
                        . '"' . $node[KEY_KEY] . '": '
                        . buildPrettyValue(
                            $node[KEY_VALUE_BEFORE] ?? $node[KEY_CHILDREN],
                            $spaces + RESPONSE_SPACES_NEXT_LEVEL
                        );

                    $result[] = generateSpaces($spaces)
                        . getPrettyStatusLabel(TYPE_ADDED)
                        . '"' . $node[KEY_KEY] . '": '
                        . buildPrettyValue(
                            $node[KEY_VALUE_AFTER] ?? $node[KEY_CHILDREN],
                            $spaces + RESPONSE_SPACES_NEXT_LEVEL
                        );
                } else {
                    $result[] = generateSpaces($spaces)
                        . getPrettyStatusLabel($node[KEY_TYPE])
                        . '"' . $node[KEY_KEY] . '": '
                        . buildPrettyValue(
                            $node[KEY_VALUE_BEFORE] ?? $node[KEY_CHILDREN],
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

function buildPrettyValue($data, $spaces)
{
    if (is_array($data)) {
        return buildPretty($data, $spaces);
    }
    if (is_bool($data)) {
        return ($data ? 'true' : 'false');
    } else {
        return '"' . $data . '"';
    }
}

function getPrettyStatusLabel(string $type): string
{
    if ($type === TYPE_ADDED) {
        return '  + ';
    } elseif ($type === TYPE_REMOVED) {
        return '  - ';
    } else {
        return '    ';
    }
}
