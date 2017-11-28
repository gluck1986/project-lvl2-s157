<?php

namespace GenDiff\Tests;

use GenDiff\GenDiffException;
use PHPUnit\Framework\TestCase;
use function GenDiff\Differ\genDiff;

class DifferTest extends TestCase
{

    public function testJson()
    {
        $before = __DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'before.json';
        $after = __DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'after.json';

        $result = genDiff($before, $after);
        $this->assertEquals($this->getExpected(), $result);
    }

    public function getExpected()
    {
        return <<<TEXT
{
    "host": "hexlet.io"
  - "timeout": "50"
  + "timeout": "20"
  - "proxy": "123.234.53.22"
  + "verbose": true
}
TEXT;

    }

    public function testWrongFiles()
    {
        $before = __DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'before1.json';
        $after = __DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'after.json';

        $this->expectException(GenDiffException::class);
        $this->expectExceptionMessage('Файл не найден: "' . $before . '"');
        genDiff($before, $after);
    }

    public function testEmptyFiles()
    {
        $before = __DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'empty.json';
        $after = __DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'empty.json';

        $expected = '{' . PHP_EOL . PHP_EOL . '}';
        $result = genDiff($before, $after);
        $this->assertEquals($expected, $result);
    }

    public function testYaml()
    {
        $before = __DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'before.yml';
        $after = __DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'after.yml';

        $result = genDiff($before, $after);
        $this->assertEquals($this->getExpected(), $result);
    }

    public function testMultiJson()
    {
        $before = __DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'mbefore.json';
        $after = __DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'mafter.json';

        $result = genDiff($before, $after);
        $this->assertEquals($this->getExpectedMulti(), $result);
    }

    function getExpectedMulti()
    {
        return <<<TEXT
{
    "common": {
        "setting1": "Value 1"
      - "setting2": "200"
        "setting3": true
      - "setting6": {
            "key": "value"
        }
      + "setting4": "blah blah"
      + "setting5": {
            "key5": "value5"
        }
    }
    "group1": {
      - "baz": "bas"
      + "baz": "bars"
        "foo": "bar"
    }
  - "group2": {
        "abc": "12345"
    }
  + "group3": {
        "fee": "100500"
    }
}
TEXT;

    }
}
