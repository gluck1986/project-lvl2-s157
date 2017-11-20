<?php

namespace GenDiff\Differ;

use Symfony\Component\Yaml\Yaml;

const FORMAT_JSON = 'json';
const FORMAT_YAML = 'yaml';
const FORMAT_ARRAY = 'array';

const STR_STATUS_ADD = 'add';
const STR_STATUS_REMOVE = 'rm';
const STR_STATUS_IDENTICAL = 'ident';

function genDiff($srcFirst, $srcSecond, $format = FORMAT_JSON)
{
    if ($format === FORMAT_JSON) {
        $firstArr = json_decode($srcFirst, true);
        $secondArr = json_decode($srcSecond, true);

        return buildResponse(diffArrays($firstArr, $secondArr));
    } elseif ($format === FORMAT_ARRAY) {
        return buildResponse(diffArrays($srcFirst, $srcSecond));
    } elseif ($format === FORMAT_YAML) {
        $firstArr = Yaml::parse($srcFirst, Yaml::PARSE_OBJECT_FOR_MAP);
        $secondArr = Yaml::parse($srcSecond, Yaml::PARSE_OBJECT_FOR_MAP);

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
    $resultString = implode(
        PHP_EOL,
        array_map(
            function ($itemArr) {
                return getStatusLabel($itemArr['state'])
                    . $itemArr['key'] . ': '
                    . (is_int($itemArr['value']) ? $itemArr['value'] : '"' . $itemArr['value'] . '"');
            },
            $results
        )
    );

    return '{' . PHP_EOL . $resultString . PHP_EOL . '}';
}

function getStatusLabel(string $status): string
{
    $statusLabels = [
        STR_STATUS_ADD => ' + ',
        STR_STATUS_REMOVE => ' - ',
        STR_STATUS_IDENTICAL => '   ',
    ];

    return $statusLabels[$status];
}
