<?php

namespace GenDiff\Tests;

use PHPUnit\Framework\TestCase;
use const GenDiff\ASTDefines\STATE_ADDED;
use const GenDiff\ASTDefines\STATE_IDENTICAL;
use const GenDiff\ASTDefines\STATE_NESTED_AFTER;
use const GenDiff\ASTDefines\STATE_NESTED_BEFORE;
use const GenDiff\ASTDefines\STATE_REMOVED;
use const GenDiff\ASTDefines\STATE_UPDATED;
use function GenDiff\ASTBuilder\build;

class ASTBuilderTest extends TestCase
{
    public function identDataProvider(): array
    {
        return [
            [
                ['key1' => 'val1'],
                ['key1' => 'val1'],
                [['state' => STATE_IDENTICAL, 'key' => 'key1', 'dataBefore' => 'val1', 'dataAfter' => 'val1']]
            ],
            [
                ['key1' => 'val1', 'key2' => 'val2',],
                ['key1' => 'val1', 'key2' => 'val2',],
                [
                    ['state' => STATE_IDENTICAL, 'key' => 'key1', 'dataBefore' => 'val1', 'dataAfter' => 'val1',],
                    ['state' => STATE_IDENTICAL, 'key' => 'key2', 'dataBefore' => 'val2', 'dataAfter' => 'val2'],
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
                [['state' => STATE_ADDED, 'key' => 'key1', 'dataBefore' => null, 'dataAfter' => 'val1']]
            ],
            [
                ['key1' => 'val1'],
                ['key1' => 'val1', 'key2' => 'val2',],
                [
                    ['state' => STATE_IDENTICAL, 'key' => 'key1', 'dataBefore' => 'val1', 'dataAfter' => 'val1'],
                    ['state' => STATE_ADDED, 'key' => 'key2', 'dataBefore' => null, 'dataAfter' => 'val2'],
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
                [['state' => STATE_REMOVED, 'key' => 'key1', 'dataBefore' => 'val1', 'dataAfter' => null]]
            ],
            [
                ['key1' => 'val1', 'key2' => 'val2',],
                ['key1' => 'val1'],
                [
                    ['state' => STATE_IDENTICAL, 'key' => 'key1', 'dataBefore' => 'val1', 'dataAfter' => 'val1'],
                    ['state' => STATE_REMOVED, 'key' => 'key2', 'dataBefore' => 'val2', 'dataAfter' => null],
                ]
            ],
            [
                ['key2' => 'val2', 'key1' => 'val1',],
                [],
                [
                    ['state' => STATE_REMOVED, 'key' => 'key2', 'dataBefore' => 'val2', 'dataAfter' => null],
                    ['state' => STATE_REMOVED, 'key' => 'key1', 'dataBefore' => 'val1', 'dataAfter' => null],
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
                    ['state' => STATE_REMOVED, 'key' => 'key1', 'dataBefore' => 'val1', 'dataAfter' => null],
                    ['state' => STATE_REMOVED + STATE_NESTED_BEFORE, 'key' => 'key2', 'dataBefore' => [
                        ['state' => STATE_IDENTICAL, 'key' => 'key1', 'dataBefore' => 'val1', 'dataAfter' => 'val1']
                    ],
                        'dataAfter' => null
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
                    ['state' => STATE_UPDATED + STATE_NESTED_AFTER, 'key' => 'key1', 'dataBefore' => 'val1',
                        'dataAfter' => [[
                            'state' => STATE_IDENTICAL,
                            'key' => 'key2',
                            'dataBefore' => 'val2',
                            'dataAfter' => 'val2'
                        ]]],
                    ['state' => STATE_REMOVED, 'key' => 'key2', 'dataBefore' => 'val2', 'dataAfter' => null],
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
                    ['state' => STATE_REMOVED, 'key' => 'key2', 'dataBefore' => 'val2', 'dataAfter' => null],
                    ['state' => STATE_IDENTICAL, 'key' => 'key1', 'dataBefore' => 'val1', 'dataAfter' => 'val1'],
                    ['state' => STATE_IDENTICAL, 'key' => 'key3', 'dataBefore' => 'val3', 'dataAfter' => 'val3'],
                    ['state' => STATE_IDENTICAL + STATE_NESTED_BEFORE + STATE_NESTED_AFTER,
                        'key' => 'key4', 'dataBefore' => [
                        ['state' => STATE_IDENTICAL, 'key' => 'key1', 'dataBefore' => 'val1', 'dataAfter' => 'val1'],
                        ['state' => STATE_UPDATED, 'key' => 'key2', 'dataBefore' => 'val2', 'dataAfter' => 'val33'],

                    ],
                        'dataAfter' => [
                            ['state' => STATE_IDENTICAL, 'key' => 'key1', 'dataBefore' => 'val1', 'dataAfter' => 'val1'],
                            ['state' => STATE_UPDATED, 'key' => 'key2', 'dataBefore' => 'val2', 'dataAfter' => 'val33'],
                        ],

                    ],
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

    /** @dataProvider diffMultiLevelDataProvider */
    public function testDiffMultilevel($first, $second, $excepted)
    {
        $actual = build($first, $second);
        $this->assertEquals($excepted, $actual, 'diff multilevel');
    }

}
