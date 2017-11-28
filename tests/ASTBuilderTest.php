<?php

namespace GenDiff\Tests;

use PHPUnit\Framework\TestCase;
use const GenDiff\ASTBuilder\TYPE_ADDED;
use const GenDiff\ASTBuilder\TYPE_IDENTICAL;
use const GenDiff\ASTBuilder\TYPE_REMOVED;
use const GenDiff\ASTBuilder\TYPE_UPDATED;
use function GenDiff\ASTBuilder\build;

class ASTBuilderTest extends TestCase
{
    public function identDataProvider(): array
    {
        return [
            [
                ['key1' => 'val1'],
                ['key1' => 'val1'],
                [['type' => TYPE_IDENTICAL, 'key' => 'key1', 'valBefore' => 'val1', 'valAfter' => 'val1', 'children' => null]]
            ],
            [
                ['key1' => 'val1', 'key2' => 'val2',],
                ['key1' => 'val1', 'key2' => 'val2',],
                [
                    ['type' => TYPE_IDENTICAL, 'key' => 'key1', 'valBefore' => 'val1', 'valAfter' => 'val1', 'children' => null],
                    ['type' => TYPE_IDENTICAL, 'key' => 'key2', 'valBefore' => 'val2', 'valAfter' => 'val2', 'children' => null],
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
                [['type' => TYPE_ADDED, 'key' => 'key1', 'valBefore' => null, 'valAfter' => 'val1', 'children' => null]]
            ],
            [
                ['key1' => 'val1'],
                ['key1' => 'val1', 'key2' => 'val2',],
                [
                    ['type' => TYPE_IDENTICAL, 'key' => 'key1', 'valBefore' => 'val1', 'valAfter' => 'val1', 'children' => null],
                    ['type' => TYPE_ADDED, 'key' => 'key2', 'valBefore' => null, 'valAfter' => 'val2', 'children' => null],
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
                [['type' => TYPE_REMOVED, 'key' => 'key1', 'valBefore' => 'val1', 'valAfter' => null, 'children' => null]]
            ],
            [
                ['key1' => 'val1', 'key2' => 'val2',],
                ['key1' => 'val1'],
                [
                    ['type' => TYPE_IDENTICAL, 'key' => 'key1', 'valBefore' => 'val1', 'valAfter' => 'val1', 'children' => null],
                    ['type' => TYPE_REMOVED, 'key' => 'key2', 'valBefore' => 'val2', 'valAfter' => null, 'children' => null],
                ]
            ],
            [
                ['key2' => 'val2', 'key1' => 'val1',],
                [],
                [
                    ['type' => TYPE_REMOVED, 'key' => 'key2', 'valBefore' => 'val2', 'valAfter' => null, 'children' => null],
                    ['type' => TYPE_REMOVED, 'key' => 'key1', 'valBefore' => 'val1', 'valAfter' => null, 'children' => null],
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
                    ['type' => TYPE_REMOVED, 'key' => 'key1', 'valBefore' => 'val1', 'valAfter' => null, 'children' => null],
                    ['type' => TYPE_REMOVED, 'key' => 'key2', 'valBefore' => null, 'children' => [
                        ['type' => TYPE_IDENTICAL, 'key' => 'key1', 'valBefore' => 'val1', 'valAfter' => 'val1', 'children' => null]
                    ],
                        'valAfter' => null
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
                    ['type' => TYPE_UPDATED, 'key' => 'key1', 'valBefore' => 'val1',
                        'children' => [[
                            'type' => TYPE_IDENTICAL,
                            'key' => 'key2',
                            'valBefore' => 'val2',
                            'valAfter' => 'val2',
                            'children' => null
                        ]], 'valAfter' => null],
                    ['type' => TYPE_REMOVED, 'key' => 'key2', 'valBefore' => 'val2', 'valAfter' => null, 'children' => null],
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
                    ['type' => TYPE_REMOVED, 'key' => 'key2', 'valBefore' => 'val2', 'valAfter' => null, 'children' => null],
                    ['type' => TYPE_IDENTICAL, 'key' => 'key1', 'valBefore' => 'val1', 'valAfter' => 'val1', 'children' => null],
                    ['type' => TYPE_IDENTICAL, 'key' => 'key3', 'valBefore' => 'val3', 'valAfter' => 'val3', 'children' => null],
                    ['type' => TYPE_IDENTICAL,
                        'key' => 'key4', 'valBefore' => null, 'valAfter' => null,
                        'children' => [
                            ['type' => TYPE_IDENTICAL, 'key' => 'key1', 'valBefore' => 'val1', 'valAfter' => 'val1', 'children' => null],
                            ['type' => TYPE_UPDATED, 'key' => 'key2', 'valBefore' => 'val2', 'valAfter' => 'val33', 'children' => null],
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
