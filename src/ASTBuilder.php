<?php

namespace GenDiff\ASTBuilder;

use const GenDiff\ASTDefines\STR_STATUS_ADDED;
use const GenDiff\ASTDefines\STR_STATUS_IDENTICAL;
use const GenDiff\ASTDefines\STR_STATUS_REMOVED;

function build(array $first, array $second): array
{
    return array_reduce(
        array_unique(array_merge(array_keys($first), array_keys($second))),
        function ($result, $key) use ($first, $second) {
            if (array_key_exists($key, $first) && array_key_exists($key, $second)) {
                if (is_array($first[$key]) && is_array($second[$key])) {
                    $result[] = buildIdenticalLeaf(
                        $key,
                        null,
                        build($first[$key], $second[$key])
                    );
                } elseif (is_array($first[$key])) {
                    $result[] = buildRemoveLeaf($key, null, build($first[$key], $first[$key]));
                    $result[] = buildCreatedLeaf($key, $second[$key]);
                } elseif (is_array($second[$key])) {
                    $result[] = buildRemoveLeaf($key, $first[$key]);
                    $result[] = buildCreatedLeaf($key, null, build($second[$key], $second[$key]));
                } elseif ($first[$key] !== $second[$key]) {
                    $result[] = buildRemoveLeaf($key, $first[$key]);
                    $result[] = buildCreatedLeaf($key, $second[$key]);
                } else {
                    $result[] = buildIdenticalLeaf($key, $first[$key]);
                }
            } elseif (array_key_exists($key, $first)) {
                if (is_array($first[$key])) {
                    $result[] = buildRemoveLeaf($key, null, build($first[$key], $first[$key]));
                } else {
                    $result[] = buildRemoveLeaf($key, $first[$key]);
                }
            } else {
                if (is_array($second[$key])) {
                    $result[] = buildCreatedLeaf($key, null, build($second[$key], $second[$key]));
                } else {
                    $result[] = buildCreatedLeaf($key, $second[$key]);
                }
            }

            return $result;
        },
        array()
    );
}

function buildIdenticalLeaf(string $key, $value = null, array $children = null)
{
    return buildLeaf(STR_STATUS_IDENTICAL, $key, $value, $children);
}

function buildCreatedLeaf(string $key, $secondValue = null, array $children = null)
{
    return buildLeaf(STR_STATUS_ADDED, $key, $secondValue, $children);
}

function buildRemoveLeaf(string $key, $firstValue = null, array $children = null)
{
    return buildLeaf(STR_STATUS_REMOVED, $key, $firstValue, $children);
}

/**
 * @param $state string {STR_STATUS_REMOVED, STR_STATUS_IDENTICAL, STR_STATUS_ADDED}
 * @param $key string
 * @param $value
 * @param $children array
 *
 * @return array
 * @internal param null $children
 */
function buildLeaf(string $state, string $key, $value = null, array $children = null)
{
    return compact($state, $key, $value, $children, ['state', 'key', 'value', 'children']);
}
