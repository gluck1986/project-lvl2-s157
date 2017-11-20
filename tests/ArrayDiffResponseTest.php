<?php

namespace GenDiff\Tests;


use PHPUnit\Framework\TestCase;
use const GenDiff\Differ\FORMAT_ARRAY;
use function GenDiff\Differ\genDiff;

class ArrayDiffResponseTest extends TestCase
{
    public function diffDataProvider(): array
    {
        return [
            [
                ['key2' => 'val2', 'key1' => 'val6', 'key3' => 'val3', 'key4' => 'val4',],
                ['key1' => 'val1', 'key3' => 'val3', 'key4' => 'val4', 'key9' => 123],
                '{' . PHP_EOL .
                ' - key2 : val2'. PHP_EOL .
                ' - key1 : val6'. PHP_EOL .
                ' + key1 : val1'. PHP_EOL .
                '   key3 : val3'. PHP_EOL .
                '   key4 : val4'. PHP_EOL .
                ' + key9 : 123'. PHP_EOL .
                '' .
                '}',

            ],
            [[], [], '{'. PHP_EOL . PHP_EOL . '}'],

        ];
    }

    /** @dataProvider diffDataProvider */
    public function testDiffResponseString($first, $second, $excepted)
    {
        $actual = genDiff($first, $second, FORMAT_ARRAY);
        $this->assertEquals($excepted, $actual);
    }
}
