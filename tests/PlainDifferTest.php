<?php
/**
 * Created by PhpStorm.
 * User: gluck
 * Date: 27.11.17
 * Time: 12:00
 */

namespace GenDiff\Tests;

use PHPUnit\Framework\TestCase;
use function GenDiff\Differ\genDiff;

class PlainDifferTest  extends TestCase
{
    public function testMultiJsonPlain()
    {
        $before = __DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'mbefore.json';
        $after = __DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'mafter.json';

        $result = genDiff($before, $after, 'plain');
        $this->assertEquals($this->getExpected(), $result);
    }

    protected function getExpected()
    {
        return <<<TEXT
Property 'common.setting2' was removed
Property 'common.setting6' was removed
Property 'common.setting4' was added with value: 'blah blah'
Property 'common.setting5' was added with value: 'complex value'
Property 'group1.baz' was changed. From: 'bas' to 'bars'
Property 'group2' was removed
Property 'group3' was added with value: 'complex value'

TEXT;

    }
}
