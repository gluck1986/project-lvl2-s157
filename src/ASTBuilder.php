<?php

namespace GenDiff\ASTBuilder;

use const GenDiff\ASTDefines\STR_STATUS_ADD;
use const GenDiff\ASTDefines\STR_STATUS_IDENTICAL;
use const GenDiff\ASTDefines\STR_STATUS_REMOVE;

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
                return buildCreatedLeaf($key, buildIdenticalNodes($value));
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
        return [buildIdenticalLeaf($key, build($valFirst, $valSecond))];
    } elseif (is_array($valFirst)) {
        return [
            buildRemoveLeaf($key, buildIdenticalLeaf($key, $valFirst)),
            buildCreatedLeaf($key, $valSecond)
        ];
    } else {
        return [
            buildRemoveLeaf($key, $valFirst),
            buildCreatedLeaf($key, buildIdenticalNodes($valSecond))
        ];
    }
}

function buildRemoveNode($key, $value)
{
    if (is_array($value)) {
        return [buildRemoveLeaf($key, buildIdenticalNodes($value))];
    }

    return [buildRemoveLeaf($key, $value)];
}

function buildIdenticalLeaf($key, $value)
{
    return buildResultValue(STR_STATUS_IDENTICAL, $key, $value);
}

function buildCreatedLeaf($key, $secondValue)
{
    return buildResultValue(STR_STATUS_ADD, $key, $secondValue);
}

function buildRemoveLeaf($key, $firstValue)
{
    return buildResultValue(STR_STATUS_REMOVE, $key, $firstValue);
}

/**
 * @param $state string {STR_STATUS_REMOVE, STR_STATUS_IDENTICAL, STR_STATUS_ADD}
 * @param $key string
 * @param $value
 *
 * @return array
 */
function buildResultValue($state, $key, $value)
{
    return compact($state, $key, $value, ['state', 'key', 'value']);
}
