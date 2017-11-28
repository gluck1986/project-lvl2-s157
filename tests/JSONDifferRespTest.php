<?php

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
[{"type":"nested","key":"common","dataBefore":null,"dataAfter":null,"children":[{"type":"identical","key":"setting1","dataBefore":"Value 1","dataAfter":"Value 1","children":null},{"type":"removed","key":"setting2","dataBefore":"200","dataAfter":null,"children":null},{"type":"identical","key":"setting3","dataBefore":true,"dataAfter":true,"children":null},{"type":"removed","key":"setting6","dataBefore":{"key":"value"},"dataAfter":null,"children":null},{"type":"added","key":"setting4","dataBefore":null,"dataAfter":"blah blah","children":null},{"type":"added","key":"setting5","dataBefore":null,"dataAfter":{"key5":"value5"},"children":null}]},{"type":"nested","key":"group1","dataBefore":null,"dataAfter":null,"children":[{"type":"updated","key":"baz","dataBefore":"bas","dataAfter":"bars","children":null},{"type":"identical","key":"foo","dataBefore":"bar","dataAfter":"bar","children":null}]},{"type":"removed","key":"group2","dataBefore":{"abc":"12345"},"dataAfter":null,"children":null},{"type":"added","key":"group3","dataBefore":null,"dataAfter":{"fee":"100500"},"children":null}]
JSON;
    }
}
