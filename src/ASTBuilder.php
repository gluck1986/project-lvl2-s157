<?php

namespace GenDiff\ASTBuilder;

const TYPE_ADDED = 'added';
const TYPE_REMOVED = 'removed';
const TYPE_UPDATED = 'updated';
const TYPE_IDENTICAL = 'identical';

const KEY_KEY = 'key';
const KEY_TYPE = 'type';
const KEY_VALUE_BEFORE = 'valBefore';
const KEY_VALUE_AFTER = 'valAfter';
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
function build(array $first, array $second): array
{
    return array_reduce(
        array_unique(array_merge(array_keys($first), array_keys($second))),
        function ($result, $key) use ($first, $second) {
            if (array_key_exists($key, $first) && array_key_exists($key, $second)) {
                if (is_array($first[$key]) && is_array($second[$key])) {
                    $result[] = buildLeaf(
                        TYPE_IDENTICAL,
                        $key,
                        null,
                        null,
                        build($first[$key], $second[$key])
                    );
                } elseif (is_array($first[$key])) {
                    $result[] = buildLeaf(
                        TYPE_UPDATED,
                        $key,
                        null,
                        $second[$key],
                        build($first[$key], $first[$key])
                    );
                } elseif (is_array($second[$key])) {
                    $result[] = buildLeaf(
                        TYPE_UPDATED,
                        $key,
                        $first[$key],
                        null,
                        build($second[$key], $second[$key])
                    );
                } elseif ($first[$key] !== $second[$key]) {
                    $result[] = buildLeaf(
                        TYPE_UPDATED,
                        $key,
                        $first[$key],
                        $second[$key]
                    );
                } else {
                    $result[] = buildLeaf(
                        TYPE_IDENTICAL,
                        $key,
                        $first[$key],
                        $second[$key]
                    );
                }
            } elseif (array_key_exists($key, $first)) {
                if (is_array($first[$key])) {
                    $result[] = buildLeaf(
                        TYPE_REMOVED,
                        $key,
                        null,
                        null,
                        build($first[$key], $first[$key])
                    );
                } else {
                    $result[] = buildLeaf(
                        TYPE_REMOVED,
                        $key,
                        $first[$key],
                        null
                    );
                }
            } else {
                if (is_array($second[$key])) {
                    $result[] = buildLeaf(
                        TYPE_ADDED,
                        $key,
                        null,
                        null,
                        build($second[$key], $second[$key])
                    );
                } else {
                    $result[] = buildLeaf(
                        TYPE_ADDED,
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
 * @param string $type
 * @param string $key
 * @param $valBefore
 * @param $valAfter
 * @param null | array $children
 *
 * @return array
 */
function buildLeaf(string $type, string $key, $valBefore, $valAfter, array $children = null)
{
    return compact(
        $type,
        $key,
        $valBefore,
        $valAfter,
        $children,
        [KEY_TYPE, KEY_KEY, KEY_VALUE_BEFORE, KEY_VALUE_AFTER, KEY_CHILDREN]
    );
}
