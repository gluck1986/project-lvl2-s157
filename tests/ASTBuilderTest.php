<?php

namespace GenDiff\Tests;


use PHPUnit\Framework\TestCase;
use const GenDiff\ASTDefines\STR_STATUS_ADD;
use const GenDiff\ASTDefines\STR_STATUS_IDENTICAL;
use const GenDiff\ASTDefines\STR_STATUS_REMOVE;
use function GenDiff\ASTBuilder\build;
use function GenDiff\ASTBuilder\buildResultValue;

class ASTBuilderTest extends TestCase
{

    public function testBuildResultValue()
    {
        $excepted = ['state' => STR_STATUS_IDENTICAL, 'key' => 'key', 'value' => 'val'];
        $actual = buildResultValue(STR_STATUS_IDENTICAL, 'key', 'val');
        $this->assertEquals($excepted, $actual);

    }


    public function identDataProvider(): array
    {
        return [
            [
                ['key1' => 'val1'],
                ['key1' => 'val1'],
                [['state' => STR_STATUS_IDENTICAL, 'key' => 'key1', 'value' => 'val1']]
            ],
            [
                ['key1' => 'val1', 'key2' => 'val2',],
                ['key1' => 'val1', 'key2' => 'val2',],
                [
                    ['state' => STR_STATUS_IDENTICAL, 'key' => 'key1', 'value' => 'val1'],
                    ['state' => STR_STATUS_IDENTICAL, 'key' => 'key2', 'value' => 'val2'],
                ]
            ],
            [
                ['key1' => 'val1', 'key2' => 'val2',],
                ['key2' => 'val2', 'key1' => 'val1',],
                [
                    ['state' => STR_STATUS_IDENTICAL, 'key' => 'key1', 'value' => 'val1'],
                    ['state' => STR_STATUS_IDENTICAL, 'key' => 'key2', 'value' => 'val2'],
                ]
            ],
            [
                ['key1' => 'val1', 'key2' => 'val2', 'key3' => 'val3', 'key4' => 'val4',],
                ['key2' => 'val2', 'key1' => 'val1', 'key3' => 'val3', 'key4' => 'val4',],
                [
                    ['state' => STR_STATUS_IDENTICAL, 'key' => 'key1', 'value' => 'val1'],
                    ['state' => STR_STATUS_IDENTICAL, 'key' => 'key2', 'value' => 'val2'],
                    ['state' => STR_STATUS_IDENTICAL, 'key' => 'key3', 'value' => 'val3'],
                    ['state' => STR_STATUS_IDENTICAL, 'key' => 'key4', 'value' => 'val4'],
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
                [['state' => STR_STATUS_ADD, 'key' => 'key1', 'value' => 'val1']]
            ],
            [
                ['key1' => 'val1'],
                ['key1' => 'val1', 'key2' => 'val2',],
                [
                    ['state' => STR_STATUS_IDENTICAL, 'key' => 'key1', 'value' => 'val1'],
                    ['state' => STR_STATUS_ADD, 'key' => 'key2', 'value' => 'val2'],
                ]
            ],
            [
                [],
                ['key2' => 'val2', 'key1' => 'val1',],
                [
                    ['state' => STR_STATUS_ADD, 'key' => 'key2', 'value' => 'val2'],
                    ['state' => STR_STATUS_ADD, 'key' => 'key1', 'value' => 'val1'],
                ]
            ],
            [
                ['key1' => 'val1', 'key3' => 'val3', 'key4' => 'val4',],
                ['key2' => 'val2', 'key1' => 'val1', 'key3' => 'val3', 'key4' => 'val4',],
                [
                    ['state' => STR_STATUS_IDENTICAL, 'key' => 'key1', 'value' => 'val1'],
                    ['state' => STR_STATUS_IDENTICAL, 'key' => 'key3', 'value' => 'val3'],
                    ['state' => STR_STATUS_IDENTICAL, 'key' => 'key4', 'value' => 'val4'],
                    ['state' => STR_STATUS_ADD, 'key' => 'key2', 'value' => 'val2'],
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
                [['state' => STR_STATUS_REMOVE, 'key' => 'key1', 'value' => 'val1']]
            ],
            [
                ['key1' => 'val1', 'key2' => 'val2',],
                ['key1' => 'val1'],
                [
                    ['state' => STR_STATUS_IDENTICAL, 'key' => 'key1', 'value' => 'val1'],
                    ['state' => STR_STATUS_REMOVE, 'key' => 'key2', 'value' => 'val2'],
                ]
            ],
            [
                ['key2' => 'val2', 'key1' => 'val1',],
                [],
                [
                    ['state' => STR_STATUS_REMOVE, 'key' => 'key2', 'value' => 'val2'],
                    ['state' => STR_STATUS_REMOVE, 'key' => 'key1', 'value' => 'val1'],
                ]
            ],
            [
                ['key2' => 'val2', 'key1' => 'val1', 'key3' => 'val3', 'key4' => 'val4',],
                ['key1' => 'val1', 'key3' => 'val3', 'key4' => 'val4',],

                [
                    ['state' => STR_STATUS_REMOVE, 'key' => 'key2', 'value' => 'val2'],
                    ['state' => STR_STATUS_IDENTICAL, 'key' => 'key1', 'value' => 'val1'],
                    ['state' => STR_STATUS_IDENTICAL, 'key' => 'key3', 'value' => 'val3'],
                    ['state' => STR_STATUS_IDENTICAL, 'key' => 'key4', 'value' => 'val4'],
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
                    ['state' => STR_STATUS_REMOVE, 'key' => 'key1', 'value' => 'val1'],
                    ['state' => STR_STATUS_REMOVE, 'key' => 'key2', 'value' => [
                        ['state' => STR_STATUS_IDENTICAL, 'key' => 'key1', 'value' => 'val1']
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
                    ['state' => STR_STATUS_REMOVE, 'key' => 'key1', 'value' => 'val1'],
                    ['state' => STR_STATUS_ADD, 'key' => 'key1', 'value' => [
                        ['state' => STR_STATUS_IDENTICAL, 'key' => 'key2', 'value' => 'val2'],
                    ]],
                    ['state' => STR_STATUS_REMOVE, 'key' => 'key2', 'value' => 'val2'],
                ]
            ],
            [
                ['key2' => 'val2', 'key1' => 'val1',],
                [],
                [
                    ['state' => STR_STATUS_REMOVE, 'key' => 'key2', 'value' => 'val2'],
                    ['state' => STR_STATUS_REMOVE, 'key' => 'key1', 'value' => 'val1'],
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
                    ['state' => STR_STATUS_REMOVE, 'key' => 'key2', 'value' => 'val2'],
                    ['state' => STR_STATUS_IDENTICAL, 'key' => 'key1', 'value' => 'val1'],
                    ['state' => STR_STATUS_IDENTICAL, 'key' => 'key3', 'value' => 'val3'],
                    ['state' => STR_STATUS_IDENTICAL, 'key' => 'key4', 'value' => [
                        ['state' => STR_STATUS_IDENTICAL, 'key' => 'key1', 'value' => 'val1'],
                        ['state' => STR_STATUS_REMOVE, 'key' => 'key2', 'value' => 'val2'],
                        ['state' => STR_STATUS_ADD, 'key' => 'key2', 'value' => 'val33'],

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
                    ['state' => STR_STATUS_REMOVE, 'key' => 'key1', 'value' => 'val1'],
                    ['state' => STR_STATUS_ADD, 'key' => 'key1', 'value' => 'val2']
                ]
            ],
            [
                ['key1' => 'val1', 'key2' => 'val2',],
                ['key1' => 'val2'],
                [
                    ['state' => STR_STATUS_REMOVE, 'key' => 'key1', 'value' => 'val1'],
                    ['state' => STR_STATUS_ADD, 'key' => 'key1', 'value' => 'val2'],
                    ['state' => STR_STATUS_REMOVE, 'key' => 'key2', 'value' => 'val2'],
                ]
            ],
            [
                ['key2' => 'val2', 'key1' => 'val6', 'key3' => 'val3', 'key4' => 'val4',],
                ['key1' => 'val1', 'key3' => 'val3', 'key4' => 'val4',],

                [
                    ['state' => STR_STATUS_REMOVE, 'key' => 'key2', 'value' => 'val2'],
                    ['state' => STR_STATUS_REMOVE, 'key' => 'key1', 'value' => 'val6'],
                    ['state' => STR_STATUS_ADD, 'key' => 'key1', 'value' => 'val1'],
                    ['state' => STR_STATUS_IDENTICAL, 'key' => 'key3', 'value' => 'val3'],
                    ['state' => STR_STATUS_IDENTICAL, 'key' => 'key4', 'value' => 'val4'],
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
