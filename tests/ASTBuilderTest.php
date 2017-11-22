<?php

namespace GenDiff\Tests;


use PHPUnit\Framework\TestCase;
use const GenDiff\ASTDefines\STR_STATUS_ADDED;
use const GenDiff\ASTDefines\STR_STATUS_IDENTICAL;
use const GenDiff\ASTDefines\STR_STATUS_REMOVED;
use function GenDiff\ASTBuilder\build;
use function GenDiff\ASTBuilder\buildResultValue;

class ASTBuilderTest extends TestCase
{

    public function testBuildResultValue()
    {
        $excepted = ['state' => STR_STATUS_IDENTICAL, 'key' => 'key', 'value' => 'val', 'children' => null];
        $actual = buildResultValue(STR_STATUS_IDENTICAL, 'key', 'val');
        $this->assertEquals($excepted, $actual);
    }


    public function identDataProvider(): array
    {
        return [
            [
                ['key1' => 'val1'],
                ['key1' => 'val1'],
                [['state' => STR_STATUS_IDENTICAL, 'key' => 'key1', 'value' => 'val1', 'children' => null]]
            ],
            [
                ['key1' => 'val1', 'key2' => 'val2',],
                ['key1' => 'val1', 'key2' => 'val2',],
                [
                    ['state' => STR_STATUS_IDENTICAL, 'key' => 'key1', 'value' => 'val1', 'children' => null],
                    ['state' => STR_STATUS_IDENTICAL, 'key' => 'key2', 'value' => 'val2', 'children' => null],
                ]
            ],
            [
                ['key1' => 'val1', 'key2' => 'val2',],
                ['key2' => 'val2', 'key1' => 'val1',],
                [
                    ['state' => STR_STATUS_IDENTICAL, 'key' => 'key1', 'value' => 'val1', 'children' => null],
                    ['state' => STR_STATUS_IDENTICAL, 'key' => 'key2', 'value' => 'val2', 'children' => null],
                ]
            ],
            [
                ['key1' => 'val1', 'key2' => 'val2', 'key3' => 'val3', 'key4' => 'val4'],
                ['key2' => 'val2', 'key1' => 'val1', 'key3' => 'val3', 'key4' => 'val4'],
                [
                    ['state' => STR_STATUS_IDENTICAL, 'key' => 'key1', 'value' => 'val1', 'children' => null],
                    ['state' => STR_STATUS_IDENTICAL, 'key' => 'key2', 'value' => 'val2', 'children' => null],
                    ['state' => STR_STATUS_IDENTICAL, 'key' => 'key3', 'value' => 'val3', 'children' => null],
                    ['state' => STR_STATUS_IDENTICAL, 'key' => 'key4', 'value' => 'val4', 'children' => null],
                ]
            ],
            [[], [], []],

        ];
    }

    public function createDataProvider(): array
    {
        return [
            [
                [],
                ['key1' => 'val1'],
                [['state' => STR_STATUS_ADDED, 'key' => 'key1', 'value' => 'val1', 'children' => null]]
            ],
            [
                ['key1' => 'val1'],
                ['key1' => 'val1', 'key2' => 'val2',],
                [
                    ['state' => STR_STATUS_IDENTICAL, 'key' => 'key1', 'value' => 'val1', 'children' => null],
                    ['state' => STR_STATUS_ADDED, 'key' => 'key2', 'value' => 'val2', 'children' => null],
                ]
            ],
            [
                [],
                ['key2' => 'val2', 'key1' => 'val1',],
                [
                    ['state' => STR_STATUS_ADDED, 'key' => 'key2', 'value' => 'val2', 'children' => null],
                    ['state' => STR_STATUS_ADDED, 'key' => 'key1', 'value' => 'val1', 'children' => null],
                ]
            ],
            [
                ['key1' => 'val1', 'key3' => 'val3', 'key4' => 'val4',],
                ['key2' => 'val2', 'key1' => 'val1', 'key3' => 'val3', 'key4' => 'val4',],
                [
                    ['state' => STR_STATUS_IDENTICAL, 'key' => 'key1', 'value' => 'val1', 'children' => null],
                    ['state' => STR_STATUS_IDENTICAL, 'key' => 'key3', 'value' => 'val3', 'children' => null],
                    ['state' => STR_STATUS_IDENTICAL, 'key' => 'key4', 'value' => 'val4', 'children' => null],
                    ['state' => STR_STATUS_ADDED, 'key' => 'key2', 'value' => 'val2', 'children' => null],
                ]
            ],
            [[], [], []],

        ];
    }

    public function deleteDataProvider(): array
    {
        return [
            [
                ['key1' => 'val1'],
                [],
                [['state' => STR_STATUS_REMOVED, 'key' => 'key1', 'value' => 'val1', 'children' => null]]
            ],
            [
                ['key1' => 'val1', 'key2' => 'val2',],
                ['key1' => 'val1'],
                [
                    ['state' => STR_STATUS_IDENTICAL, 'key' => 'key1', 'value' => 'val1', 'children' => null],
                    ['state' => STR_STATUS_REMOVED, 'key' => 'key2', 'value' => 'val2', 'children' => null],
                ]
            ],
            [
                ['key2' => 'val2', 'key1' => 'val1',],
                [],
                [
                    ['state' => STR_STATUS_REMOVED, 'key' => 'key2', 'value' => 'val2', 'children' => null],
                    ['state' => STR_STATUS_REMOVED, 'key' => 'key1', 'value' => 'val1', 'children' => null],
                ]
            ],
            [
                ['key2' => 'val2', 'key1' => 'val1', 'key3' => 'val3', 'key4' => 'val4',],
                ['key1' => 'val1', 'key3' => 'val3', 'key4' => 'val4',],

                [
                    ['state' => STR_STATUS_REMOVED, 'key' => 'key2', 'value' => 'val2', 'children' => null],
                    ['state' => STR_STATUS_IDENTICAL, 'key' => 'key1', 'value' => 'val1', 'children' => null],
                    ['state' => STR_STATUS_IDENTICAL, 'key' => 'key3', 'value' => 'val3', 'children' => null],
                    ['state' => STR_STATUS_IDENTICAL, 'key' => 'key4', 'value' => 'val4', 'children' => null],
                ]
            ],
            [[], [], []],

        ];
    }

    public function diffMultiLevelDataProvider(): array
    {
        return [
            [
                ['key1' => 'val1', 'key2' => ['key1' => 'val1']],
                [],
                [
                    ['state' => STR_STATUS_REMOVED, 'key' => 'key1', 'value' => 'val1', 'children' => null],
                    ['state' => STR_STATUS_REMOVED, 'key' => 'key2', 'value' => null, 'children' => [
                        ['state' => STR_STATUS_IDENTICAL, 'key' => 'key1', 'value' => 'val1', 'children' => null]
                    ]
                    ]
                ]
            ],
            [
                ['key1' => 'val1', 'key2' => 'val2',],
                [
                    'key1' => [
                        'key2' => 'val2'
                    ]
                ],
                [
                    ['state' => STR_STATUS_REMOVED, 'key' => 'key1', 'value' => 'val1', 'children' => null],
                    ['state' => STR_STATUS_ADDED, 'key' => 'key1', 'value' => null, 'children' => [
                        ['state' => STR_STATUS_IDENTICAL, 'key' => 'key2', 'value' => 'val2', 'children' => null],
                    ]],
                    ['state' => STR_STATUS_REMOVED, 'key' => 'key2', 'value' => 'val2', 'children' => null],
                ]
            ],
            [
                ['key2' => 'val2', 'key1' => 'val1',],
                [],
                [
                    ['state' => STR_STATUS_REMOVED, 'key' => 'key2', 'value' => 'val2', 'children' => null],
                    ['state' => STR_STATUS_REMOVED, 'key' => 'key1', 'value' => 'val1', 'children' => null],
                ]
            ],
            [
                ['key2' => 'val2', 'key1' => 'val1', 'key3' => 'val3', 'key4' => [
                    'key1' => 'val1', 'key2' => 'val2'
                ],],
                ['key1' => 'val1', 'key3' => 'val3', 'key4' => [
                    'key1' => 'val1', 'key2' => 'val33'
                ],],

                [
                    ['state' => STR_STATUS_REMOVED, 'key' => 'key2', 'value' => 'val2', 'children' => null],
                    ['state' => STR_STATUS_IDENTICAL, 'key' => 'key1', 'value' => 'val1', 'children' => null],
                    ['state' => STR_STATUS_IDENTICAL, 'key' => 'key3', 'value' => 'val3', 'children' => null],
                    ['state' => STR_STATUS_IDENTICAL, 'key' => 'key4', 'value' => null, 'children' => [
                        ['state' => STR_STATUS_IDENTICAL, 'key' => 'key1', 'value' => 'val1', 'children' => null],
                        ['state' => STR_STATUS_REMOVED, 'key' => 'key2', 'value' => 'val2', 'children' => null],
                        ['state' => STR_STATUS_ADDED, 'key' => 'key2', 'value' => 'val33', 'children' => null],

                    ]],
                ]
            ],
            [[], [], []],

        ];
    }

    public function diffDataProvider(): array
    {
        return [
            [
                ['key1' => 'val1'],
                ['key1' => 'val2'],
                [
                    ['state' => STR_STATUS_REMOVED, 'key' => 'key1', 'value' => 'val1', 'children' => null],
                    ['state' => STR_STATUS_ADDED, 'key' => 'key1', 'value' => 'val2', 'children' => null]
                ]
            ],
            [
                ['key1' => 'val1', 'key2' => 'val2',],
                ['key1' => 'val2'],
                [
                    ['state' => STR_STATUS_REMOVED, 'key' => 'key1', 'value' => 'val1', 'children' => null],
                    ['state' => STR_STATUS_ADDED, 'key' => 'key1', 'value' => 'val2', 'children' => null],
                    ['state' => STR_STATUS_REMOVED, 'key' => 'key2', 'value' => 'val2', 'children' => null],
                ]
            ],
            [
                ['key2' => 'val2', 'key1' => 'val6', 'key3' => 'val3', 'key4' => 'val4',],
                ['key1' => 'val1', 'key3' => 'val3', 'key4' => 'val4',],

                [
                    ['state' => STR_STATUS_REMOVED, 'key' => 'key2', 'value' => 'val2', 'children' => null],
                    ['state' => STR_STATUS_REMOVED, 'key' => 'key1', 'value' => 'val6', 'children' => null],
                    ['state' => STR_STATUS_ADDED, 'key' => 'key1', 'value' => 'val1', 'children' => null],
                    ['state' => STR_STATUS_IDENTICAL, 'key' => 'key3', 'value' => 'val3', 'children' => null],
                    ['state' => STR_STATUS_IDENTICAL, 'key' => 'key4', 'value' => 'val4', 'children' => null],
                ]
            ],
            [[], [], []],

        ];
    }

    /** @dataProvider identDataProvider */
    public function testIdent($first, $second, $excepted)
    {
        $actual = build($first, $second);
        $this->assertEquals($excepted, $actual, 'ident');
    }

    /** @dataProvider createDataProvider */
    public function testCreated($first, $second, $excepted)
    {
        $actual = build($first, $second);
        $this->assertEquals($excepted, $actual, 'create');
    }

    /** @dataProvider deleteDataProvider */
    public function testDeleted($first, $second, $excepted)
    {
        $actual = build($first, $second);
        $this->assertEquals($excepted, $actual, 'delete');
    }

    /** @dataProvider diffDataProvider */
    public function testDiff($first, $second, $excepted)
    {

        $actual = build($first, $second);
        $this->assertEquals($excepted, $actual, 'diff');
    }

    /** @dataProvider diffMultiLevelDataProvider */
    public function testDiffMultilevel($first, $second, $excepted)
    {
        $actual = build($first, $second);
        $this->assertEquals($excepted, $actual, 'diff multilevel');
    }

}
