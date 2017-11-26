<?php

namespace GenDiff\ASTBuilder;

use const GenDiff\ASTDefines\KEY_DATA_AFTER;
use const GenDiff\ASTDefines\KEY_DATA_BEFORE;
use const GenDiff\ASTDefines\KEY_KEY;
use const GenDiff\ASTDefines\KEY_STATE;
use const GenDiff\ASTDefines\STATE_ADDED;
use const GenDiff\ASTDefines\STATE_IDENTICAL;
use const GenDiff\ASTDefines\STATE_NESTED_AFTER;
use const GenDiff\ASTDefines\STATE_NESTED_BEFORE;
use const GenDiff\ASTDefines\STATE_REMOVED;
use const GenDiff\ASTDefines\STATE_UPDATED;

function build(array $first, array $second): array
{
    return array_reduce(
        array_unique(array_merge(array_keys($first), array_keys($second))),
        function ($result, $key) use ($first, $second) {
            if (array_key_exists($key, $first) && array_key_exists($key, $second)) {
                if (is_array($first[$key]) && is_array($second[$key])) {
                    $result[] = buildLeaf(
                        STATE_IDENTICAL + STATE_NESTED_BEFORE + STATE_NESTED_AFTER,
                        $key,
                        build($first[$key], $second[$key]),
                        build($first[$key], $second[$key])
                    );
                } elseif (is_array($first[$key])) {
                    $result[] = buildLeaf(
                        STATE_UPDATED + STATE_NESTED_BEFORE,
                        $key,
                        build($first[$key], $first[$key]),
                        $second[$key]
                    );
                } elseif (is_array($second[$key])) {
                    $result[] = buildLeaf(
                        STATE_UPDATED + STATE_NESTED_AFTER,
                        $key,
                        $first[$key],
                        build($second[$key], $second[$key])
                    );
                } elseif ($first[$key] !== $second[$key]) {
                    $result[] = buildLeaf(
                        STATE_UPDATED,
                        $key,
                        $first[$key],
                        $second[$key]
                    );
                } else {
                    $result[] = buildLeaf(
                        STATE_IDENTICAL,
                        $key,
                        $first[$key],
                        $second[$key]
                    );
                }
            } elseif (array_key_exists($key, $first)) {
                if (is_array($first[$key])) {
                    $result[] = buildLeaf(
                        STATE_REMOVED + STATE_NESTED_BEFORE,
                        $key,
                        build($first[$key], $first[$key]),
                        null
                    );
                } else {
                    $result[] = buildLeaf(
                        STATE_REMOVED,
                        $key,
                        $first[$key],
                        null
                    );
                }
            } else {
                if (is_array($second[$key])) {
                    $result[] = buildLeaf(
                        STATE_ADDED + STATE_NESTED_AFTER,
                        $key,
                        null,
                        build($second[$key], $second[$key])
                    );
                } else {
                    $result[] = buildLeaf(
                        STATE_ADDED,
                        $key,
                        null,
                        $second[$key]
                    );
                }
            }

            return $result;
        },
        array()
    );
}

/**
 * @param $state int
 * @param $key string
 * @param $dataBefore
 * @param $dataAfter
 *
 * @return array
 */
function buildLeaf(int $state, string $key, $dataBefore, $dataAfter)
{
    return compact($state, $key, $dataBefore, $dataAfter, [KEY_STATE, KEY_KEY, KEY_DATA_BEFORE, KEY_DATA_AFTER]);
}
