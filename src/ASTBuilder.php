<?php

namespace GenDiff\ASTBuilder;

const TYPE_ADDED = 'added';
const TYPE_REMOVED = 'removed';
const TYPE_UPDATED = 'updated';
const TYPE_IDENTICAL = 'identical';
const TYPE_NESTED = 'nested';

const KEY_KEY = 'key';
const KEY_TYPE = 'type';
const KEY_DATA_BEFORE = 'dataBefore';
const KEY_DATA_AFTER = 'dataAfter';
const KEY_CHILDREN = 'children';

const FORMAT_PRETTY = 'pretty';
const FORMAT_PLAIN = 'plain';
const FORMAT_JSON = 'json';

/**
 * @param array $first
 * @param array $second
 *
 * @return array
 */
function buildAST(array $first, array $second): array
{
    $uniqueKeys = array_unique(array_merge(array_keys($first), array_keys($second)));

    return array_reduce(
        $uniqueKeys,
        function ($result, $key) use ($first, $second) {
            if (array_key_exists($key, $first) && array_key_exists($key, $second)) {
                if (is_array($first[$key]) && is_array($second[$key])) {
                    $result[] = buildLeaf(
                        TYPE_NESTED,
                        $key,
                        null,
                        null,
                        buildAST($first[$key], $second[$key])
                    );
                } else {
                    $result[] = buildLeaf(
                        $first[$key] !== $second[$key] ? TYPE_UPDATED : TYPE_IDENTICAL,
                        $key,
                        $first[$key],
                        $second[$key]
                    );
                }
            } elseif (array_key_exists($key, $first)) {
                $result[] = buildLeaf(
                    TYPE_REMOVED,
                    $key,
                    $first[$key],
                    null
                );
            } else {
                $result[] = buildLeaf(
                    TYPE_ADDED,
                    $key,
                    null,
                    $second[$key]
                );
            }

            return $result;
        },
        array()
    );
}


/**
 * @param string $type
 * @param string $key
 * @param $valBefore
 * @param $valAfter
 * @param null | array $children
 *
 * @return array
 */
function buildLeaf(string $type, string $key, $dataBefore, $dataAfter, $children = null)
{
    return compact(
        $type,
        $key,
        $dataBefore,
        $dataAfter,
        $children,
        [KEY_TYPE, KEY_KEY, KEY_DATA_BEFORE, KEY_DATA_AFTER, KEY_CHILDREN]
    );
}
