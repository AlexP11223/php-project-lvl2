<?php

namespace Differ;

use PHPUnit\Framework\TestCase;

class DiffTest extends TestCase
{
    private static function getFixtureFilePath($relativeFilePath)
    {
        return dirname(__FILE__) . '/fixtures/' . $relativeFilePath;
    }

    private static function checkDiff($filePath1, $filePath2, $expectedDiffFilePath)
    {
        $expectedDiff = trim(file_get_contents(self::getFixtureFilePath($expectedDiffFilePath)));
        $diff = genDiffForFiles(self::getFixtureFilePath($filePath1), self::getFixtureFilePath($filePath2));
        self::assertEquals($expectedDiff, $diff);
    }

    public function testNormalJson()
    {
        self::checkDiff('1.json', '2.json', '1_2.diff');
        self::checkDiff('2.json', '1.json', '2_1.diff');
    }

    public function testNormalYaml()
    {
        self::checkDiff('1.yaml', '2.yaml', '1_2.diff');
        self::checkDiff('2.yaml', '1.yaml', '2_1.diff');
    }

    public function testSame()
    {
        self::checkDiff('1.json', '1.json', '1_1.diff');
    }
}
