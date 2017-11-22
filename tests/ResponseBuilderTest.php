<?php

namespace GenDiff\Tests;


use PHPUnit\Framework\TestCase;
use const GenDiff\ASTDefines\STR_STATUS_ADDED;
use const GenDiff\ASTDefines\STR_STATUS_IDENTICAL;
use const GenDiff\ASTDefines\STR_STATUS_REMOVED;
use function GenDiff\ResponseBuilder\buildResponse;

class ResponseBuilderTest extends TestCase
{
    public function diffMultiDataProvider(): array
    {
        return [[
            [
                ['state' => STR_STATUS_REMOVED, 'key' => 'key2', 'value' => 'val2', 'children' => null],
                ['state' => STR_STATUS_IDENTICAL, 'key' => 'key1', 'value' => 'val1', 'children' => null],
                ['state' => STR_STATUS_IDENTICAL, 'key' => 'key3', 'value' => 'val3', 'children' => null],
                ['state' => STR_STATUS_IDENTICAL, 'key' => 'key4', 'value' => null, 'children' => [
                    ['state' => STR_STATUS_IDENTICAL, 'key' => 'key1', 'value' => 'val1', 'children' => null],
                    ['state' => STR_STATUS_REMOVED, 'key' => 'key2', 'value' => 'val2', 'children' => null],
                    ['state' => STR_STATUS_ADDED, 'key' => 'key2', 'value' => 'val33', 'children' => null],

                ]],
            ],

            '{
  - "key2": "val2"
    "key1": "val1"
    "key3": "val3"
    "key4": {
        "key1": "val1"
      - "key2": "val2"
      + "key2": "val33"
    }
}',
        ]];
    }

    public function diffDataProvider(): array
    {
        return [
            [
                [['state' => STR_STATUS_REMOVED, 'key' => 'key2', 'value' => 'val2', 'children' => null],
                    ['state' => STR_STATUS_REMOVED, 'key' => 'key1', 'value' => 'val6', 'children' => null],
                    ['state' => STR_STATUS_ADDED, 'key' => 'key1', 'value' => 'val1', 'children' => null],
                    ['state' => STR_STATUS_IDENTICAL, 'key' => 'key3', 'value' => 'val3', 'children' => null],
                    ['state' => STR_STATUS_IDENTICAL, 'key' => 'key4', 'value' => 'val4', 'children' => null],],
                '{' . PHP_EOL .
                '  - "key2": "val2"' . PHP_EOL .
                '  - "key1": "val6"' . PHP_EOL .
                '  + "key1": "val1"' . PHP_EOL .
                '    "key3": "val3"' . PHP_EOL .
                '    "key4": "val4"' . PHP_EOL .
                '' .
                '}',

            ],
            [[], '{' . PHP_EOL . PHP_EOL . '}'],

        ];
    }

    /** @dataProvider diffDataProvider */
    public function testDiffResponseString($ast, $excepted)
    {
        $actual = buildResponse($ast);
        $this->assertEquals($excepted, $actual);
    }

    /** @dataProvider diffMultiDataProvider */
    public function testDiffMultiResponseString($ast, $excepted)
    {
        $actual = buildResponse($ast);
        $this->assertEquals($excepted, $actual);
    }
}
