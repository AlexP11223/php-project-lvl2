<?php

namespace Differ;

use PHPUnit\Framework\TestCase;

class DiffTest extends TestCase
{
    private static function getFixtureFilePath($relativeFilePath)
    {
        return dirname(__FILE__) . '/fixtures/' . $relativeFilePath;
    }

    private static function checkDiff($filePath1, $filePath2, $expectedDiffFilePath, $format = 'pretty')
    {
        $expectedDiff = trim(file_get_contents(self::getFixtureFilePath($expectedDiffFilePath)));
        $diff = genDiffForFiles(self::getFixtureFilePath($filePath1), self::getFixtureFilePath($filePath2), $format);
        self::assertEquals($expectedDiff, $diff);
    }

    public function testBasicJson()
    {
        self::checkDiff('basic1.json', 'basic2.json', 'basic1_basic2.diff');
        self::checkDiff('basic2.json', 'basic1.json', 'basic2_basic1.diff');
    }

    public function testComplexJson()
    {
        self::checkDiff('complex1.json', 'complex2.json', 'complex1_complex2.diff');
    }

    public function testBasicYaml()
    {
        self::checkDiff('basic1.yaml', 'basic2.yaml', 'basic1_basic2.diff');
        self::checkDiff('basic2.yaml', 'basic1.yaml', 'basic2_basic1.diff');
    }

    public function testComplexYaml()
    {
        self::checkDiff('complex1.yaml', 'complex2.json', 'complex1_complex2.diff');
    }

    public function testSame()
    {
        self::checkDiff('basic1.json', 'basic1.json', 'basic1_basic1.diff');
    }

    public function testPlain()
    {
        self::checkDiff('basic1.json', 'basic2.json', 'basic1_basic2.diff.plain', 'plain');
        self::checkDiff('complex1.json', 'complex2.json', 'complex1_complex2.diff.plain', 'plain');
    }

    public function testJson()
    {
        self::checkDiff('complex1.json', 'complex2.json', 'complex1_complex2.diff.json', 'json');
    }
}
