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
[{"key":"common","type":"identical","valBefore":null,"valAfter":null,"children":[{"type":"identical","key":"setting1","valBefore":"Value 1","valAfter":"Value 1","children":null},{"type":"removed","key":"setting2","valBefore":"200","valAfter":null,"children":null},{"type":"identical","key":"setting3","valBefore":true,"valAfter":true,"children":null},{"key":"setting6","type":"removed","valBefore":null,"valAfter":null,"children":[{"key":"key","type":"identical","valBefore":"value","valAfter":"value","children":null}]},{"type":"added","key":"setting4","valBefore":null,"valAfter":"blah blah","children":null},{"type":"added","key":"setting5","valBefore":null,"valAfter":null,"children":[{"type":"identical","key":"key5","valBefore":"value5","valAfter":"value5","children":null}]}]},{"type":"identical","key":"group1","valBefore":null,"valAfter":null,"children":[{"type":"updated","key":"baz","valBefore":"bas","valAfter":"bars","children":null},{"type":"identical","key":"foo","valBefore":"bar","valAfter":"bar","children":null}]},{"type":"removed","key":"group2","valBefore":null,"valAfter":null,"children":[{"type":"identical","key":"abc","valBefore":"12345","valAfter":"12345","children":null}]},{"type":"added","key":"group3","valBefore":null,"valAfter":null,"children":[{"type":"identical","key":"fee","valBefore":"100500","valAfter":"100500","children":null}]}]
JSON;
    }
}
