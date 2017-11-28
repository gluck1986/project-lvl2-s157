<?php
/**
 * Created by PhpStorm.
 * User: gluck
 * Date: 27.11.17
 * Time: 14:25
 */

namespace GenDiff\Tests;


use PHPUnit\Framework\TestCase;
use function GenDiff\Differ\genDiff;

class JSONDifferRespTest extends TestCase
{
    public function testMultiJsonPlain()
    {
        $before = __DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'mbefore.json';
        $after = __DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'mafter.json';

        $result = genDiff($before, $after, 'json');
        $this->assertEquals($this->getExpected(), $result);
    }

    protected function getExpected()
    {
        return <<<JSON
[{"key":"common","state":1118208,"dataBefore":[{"state":4096,"key":"setting1","dataBefore":"Value 1","dataAfter":"Value 1"},{"state":16,"key":"setting2","dataBefore":"200","dataAfter":null},{"state":4096,"key":"setting3","dataBefore":true,"dataAfter":true},{"key":"setting6","state":65552,"dataBefore":[{"key":"key","state":4096,"dataBefore":"value","dataAfter":"value"}],"dataAfter":null},{"state":1,"key":"setting4","dataBefore":null,"dataAfter":"blah blah"},{"state":1048577,"key":"setting5","dataBefore":null,"dataAfter":[{"state":4096,"key":"key5","dataBefore":"value5","dataAfter":"value5"}]}],"dataAfter":[{"state":4096,"key":"setting1","dataBefore":"Value 1","dataAfter":"Value 1"},{"state":16,"key":"setting2","dataBefore":"200","dataAfter":null},{"state":4096,"key":"setting3","dataBefore":true,"dataAfter":true},{"key":"setting6","state":65552,"dataBefore":[{"key":"key","state":4096,"dataBefore":"value","dataAfter":"value"}],"dataAfter":null},{"state":1,"key":"setting4","dataBefore":null,"dataAfter":"blah blah"},{"state":1048577,"key":"setting5","dataBefore":null,"dataAfter":[{"state":4096,"key":"key5","dataBefore":"value5","dataAfter":"value5"}]}]},{"state":1118208,"key":"group1","dataBefore":[{"state":256,"key":"baz","dataBefore":"bas","dataAfter":"bars"},{"state":4096,"key":"foo","dataBefore":"bar","dataAfter":"bar"}],"dataAfter":[{"state":256,"key":"baz","dataBefore":"bas","dataAfter":"bars"},{"state":4096,"key":"foo","dataBefore":"bar","dataAfter":"bar"}]},{"state":65552,"key":"group2","dataBefore":[{"state":4096,"key":"abc","dataBefore":"12345","dataAfter":"12345"}],"dataAfter":null},{"state":1048577,"key":"group3","dataBefore":null,"dataAfter":[{"state":4096,"key":"fee","dataBefore":"100500","dataAfter":"100500"}]}]
JSON;
    }
}
