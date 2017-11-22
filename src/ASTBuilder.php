<?php

namespace GenDiff\ASTBuilder;

use const GenDiff\ASTDefines\STR_STATUS_ADDED;
use const GenDiff\ASTDefines\STR_STATUS_IDENTICAL;
use const GenDiff\ASTDefines\STR_STATUS_REMOVED;

function build(array $first, array $second): array
{
    $created = array_diff_key($second, $first);

    return array_merge(
        buildDiffNodes($first, $second),
        buildCreatedNodes($created)
    );
}

function buildDiffNodes(array $first, array $second): array
{
    $deleted = array_keys(array_diff_key($first, $second));

    return array_reduce(
        array_keys($first),
        function ($result, $key) use ($first, $second, $deleted) {
            if (in_array($key, $deleted)) {
                return array_merge($result, buildRemoveNode($key, $first[$key]));
            } else {
                return array_merge($result, buildDiffNode($key, $first[$key], $second[$key]));
            }
        },
        []
    );
}

function buildCreatedNodes($created)
{
    return array_map(
        function ($key, $value) {
            if (is_array($value)) {
                return buildCreatedLeaf($key, null, buildIdenticalNodes($value));
            }

            return buildCreatedLeaf($key, $value);
        },
        array_keys($created),
        $created
    );
}

function buildIdenticalNodes(array $values)
{
    return array_map(
        function ($key, $value) {
            if (is_array($value)) {
                return buildIdenticalLeaf($key, buildIdenticalNodes($value));
            }

            return buildIdenticalLeaf($key, $value);
        },
        array_keys($values),
        $values
    );
}

function buildDiffScalarNode($key, $valFirst, $valSecond)
{
    if ($valFirst === $valSecond) {
        return [buildIdenticalLeaf($key, $valFirst)];
    }

    return [
        buildRemoveLeaf($key, $valFirst),
        buildCreatedLeaf($key, $valSecond)
    ];
}

function buildDiffNode($key, $valFirst, $valSecond)
{
    if (is_array($valFirst) || is_array($valSecond)) {
        return buildDiffArrNode($key, $valFirst, $valSecond);
    } else {
        return buildDiffScalarNode($key, $valFirst, $valSecond);
    }
}

function buildDiffArrNode($key, $valFirst, $valSecond): array
{
    if (is_array($valFirst) && is_array($valSecond)) {
        return [buildIdenticalLeaf($key, null, build($valFirst, $valSecond))];
    } elseif (is_array($valFirst)) {
        return [
            buildRemoveLeaf($key, null, buildIdenticalLeaf($key, $valFirst)),
            buildCreatedLeaf($key, $valSecond)
        ];
    } else {
        return [
            buildRemoveLeaf($key, $valFirst),
            buildCreatedLeaf($key, null, buildIdenticalNodes($valSecond))
        ];
    }
}

function buildRemoveNode($key, $value)
{
    if (is_array($value)) {
        return [buildRemoveLeaf($key, null, buildIdenticalNodes($value))];
    }

    return [buildRemoveLeaf($key, $value)];
}

function buildIdenticalLeaf(string $key, $value = null, array $children = null)
{
    return buildResultValue(STR_STATUS_IDENTICAL, $key, $value, $children);
}

function buildCreatedLeaf(string $key, $secondValue = null, array $children = null)
{
    return buildResultValue(STR_STATUS_ADDED, $key, $secondValue, $children);
}

function buildRemoveLeaf(string $key, $firstValue = null, array $children = null)
{
    return buildResultValue(STR_STATUS_REMOVED, $key, $firstValue, $children);
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
function buildResultValue(string $state, string $key, $value = null, array $children = null)
{
    return compact($state, $key, $value, $children, ['state', 'key', 'value', 'children']);
}
