<?php

namespace GenDiff\Differ;

const FORMAT_JSON = 'json';

const STR_STATUS_ADD = 'add';
const STR_STATUS_REMOVE = 'rm';
const STR_STATUS_IDENTICAL = 'ident';

function genDiff(string $srcFirst, string $srcSecond, $format = FORMAT_JSON)
{
    if ($format === FORMAT_JSON) {
        $firstArr = json_decode($srcFirst, true);
        $secondArr = json_decode($srcSecond, true);

        return buildResponse(diffArrays($firstArr, $secondArr));
    }
}

function diffArrays(array $first, array $second): array
{
    $deleted = array_keys(array_diff_key($first, $second));
    $created = array_keys(array_diff_key($second, $first));

    return array_merge(
        array_reduce(
            array_keys($first),
            function ($result, $key) use ($first, $second, $deleted) {
                if (in_array($key, $deleted)) {
                    return array_merge($result, buildRemoveValueArr($key, $first[$key]));
                } else {
                    return array_merge($result, buildDiffValuesArr($key, $first[$key], $second[$key]));
                }
            },
            []
        ),
        array_map(
            function ($key) use ($second) {
                return buildCreatedValue($key, $second[$key]);
            },
            $created
        )
    );
}

function buildCreatedValue($key, $secondValue)
{
    return buildResultValue(STR_STATUS_ADD, $key, $secondValue);
}

function buildRemoveValueArr($key, $firstValue)
{
    return [buildResultValue(STR_STATUS_REMOVE, $key, $firstValue)];
}

function buildDiffValuesArr($key, $valFirst, $valSecond)
{
    if ($valFirst === $valSecond) {
        return [buildResultValue(STR_STATUS_IDENTICAL, $key, $valFirst)];
    }

    return [
        buildResultValue(STR_STATUS_REMOVE, $key, $valFirst),
        buildResultValue(STR_STATUS_ADD, $key, $valSecond)
    ];
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

function buildResponse(array $results): string
{
    return '';
}

function getStatusLabel(string $status): string
{
    $statusLabels = [
        STR_STATUS_ADD => '+',
        STR_STATUS_REMOVE => '-',
        STR_STATUS_IDENTICAL => '',

    ];

    return $statusLabels[$status];
}
