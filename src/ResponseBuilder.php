<?php

namespace GenDiff\ResponseBuilder;

use GenDiff\GenDiffException;
use const GenDiff\ASTBuilder\FORMAT_JSON;
use const GenDiff\ASTBuilder\FORMAT_PLAIN;
use const GenDiff\ASTBuilder\FORMAT_PRETTY;
use const GenDiff\ASTBuilder\KEY_CHILDREN;
use const GenDiff\ASTBuilder\KEY_DATA_AFTER;
use const GenDiff\ASTBuilder\KEY_DATA_BEFORE;
use const GenDiff\ASTBuilder\KEY_KEY;
use const GenDiff\ASTBuilder\KEY_TYPE;
use const GenDiff\ASTBuilder\TYPE_ADDED;
use const GenDiff\ASTBuilder\TYPE_NESTED;
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
                if (is_array($node[KEY_DATA_AFTER])) {
                    $result[] = [
                        KEY_RESPONSE_LABEL => $label,
                        KEY_RESPONSE_STATE => 'was added with value: \'complex value\''
                    ];
                } else {
                    $result[] = [
                        KEY_RESPONSE_LABEL => $label,
                        KEY_RESPONSE_STATE => 'was added with value: \'' . $node[KEY_DATA_AFTER] . '\''
                    ];
                }
            }
            if ($node[KEY_TYPE] === TYPE_UPDATED) {
                if ($node[KEY_CHILDREN] && is_null($node[KEY_DATA_BEFORE])) {
                    $result[] = [
                        KEY_RESPONSE_LABEL => $label,
                        KEY_RESPONSE_STATE => 'was changed. From: \'complex value\' to \''
                            . $node[KEY_DATA_AFTER] . '\''
                    ];
                } elseif ($node[KEY_CHILDREN] && is_null($node[KEY_DATA_AFTER])) {
                    $result[] = [
                        KEY_RESPONSE_LABEL => $label,
                        KEY_RESPONSE_STATE => 'was changed. From: \'' . $node[KEY_DATA_BEFORE] . '\''
                            . ' to \'complex value\''
                    ];
                } else {
                    $result[] = [
                        KEY_RESPONSE_LABEL => $label,
                        KEY_RESPONSE_STATE => 'was changed. From: \''
                            . $node[KEY_DATA_BEFORE] . '\''
                            . ' to \'' . $node[KEY_DATA_AFTER] . '\''
                    ];
                }
            }
            if ($node[KEY_TYPE] === TYPE_NESTED) {
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


function buildPretty(array $ast, int $spacesCount = 0): string
{
    $middleData = array_reduce(
        $ast,
        function ($result, $node) use ($spacesCount) {
            if ($node[KEY_TYPE] === TYPE_NESTED) {
                $result[] = generateSpaces($spacesCount)
                    . getPrettyStatusLabel($node[KEY_TYPE])
                    . '"' . $node[KEY_KEY] . '": '
                    . buildPretty($node[KEY_CHILDREN], $spacesCount + RESPONSE_SPACES_NEXT_LEVEL);
            } elseif ($node[KEY_TYPE] === TYPE_UPDATED) {
                $result[] = generateSpaces($spacesCount)
                    . getPrettyStatusLabel(TYPE_REMOVED)
                    . '"' . $node[KEY_KEY] . '": '
                    . buildPrettyValue(
                        $node[KEY_DATA_BEFORE],
                        $spacesCount + RESPONSE_SPACES_NEXT_LEVEL
                    );

                $result[] = generateSpaces($spacesCount)
                    . getPrettyStatusLabel(TYPE_ADDED)
                    . '"' . $node[KEY_KEY] . '": '
                    . buildPrettyValue(
                        $node[KEY_DATA_AFTER],
                        $spacesCount + RESPONSE_SPACES_NEXT_LEVEL
                    );
            } else {
                $result[] = generateSpaces($spacesCount)
                    . getPrettyStatusLabel($node[KEY_TYPE])
                    . '"' . $node[KEY_KEY] . '": '
                    . buildPrettyValue(
                        $node[KEY_DATA_BEFORE] ?? $node[KEY_DATA_AFTER],
                        $spacesCount + RESPONSE_SPACES_NEXT_LEVEL
                    );
            }

            return $result;
        },
        []
    );
    $resultString = implode(PHP_EOL, $middleData);

    return '{' . PHP_EOL . $resultString . PHP_EOL . generateSpaces($spacesCount) . '}';
}

function buildPrettyValue($data, $spacesCount)
{
    if (is_array($data)) {
        $json = json_encode($data, JSON_PRETTY_PRINT);

        return str_replace(PHP_EOL, PHP_EOL . generateSpaces($spacesCount), $json);
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
