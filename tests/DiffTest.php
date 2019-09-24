<?php

namespace Differ\Tests;

use PHPUnit\Framework\TestCase;
use function Differ\genDiffForFiles;

class DiffTest extends TestCase
{
    private static function getFixtureFilePath($relativeFilePath)
    {
        return dirname(__FILE__) . '/fixtures/' . $relativeFilePath;
    }

    private static function checkDiff($filePath1, $filePath2, $expectedDiffFilePath)
    {
        $format = pathinfo($expectedDiffFilePath, PATHINFO_EXTENSION);
        $expectedDiff = trim(file_get_contents(self::getFixtureFilePath($expectedDiffFilePath)));
        $diff = genDiffForFiles(self::getFixtureFilePath($filePath1), self::getFixtureFilePath($filePath2), $format);
        self::assertEquals($expectedDiff, $diff);
    }
    /**
     * @dataProvider diffProvider
     */
    public function testDiff($fileType, $outputFormat)
    {
        self::checkDiff("before.$fileType", "after.$fileType", "before_after.diff.$outputFormat");
    }

    public function diffProvider()
    {
        return [
            ['json', 'pretty'],
            ['json', 'plain'],
            ['json', 'json'],
            ['yaml', 'pretty'],
            ['yaml', 'plain'],
            ['yaml', 'json'],
        ];
    }

    public function testSame()
    {
        self::checkDiff("before.json", "before.json", "before_before.diff.pretty");
        self::checkDiff("before.json", "before.json", "before_before.diff.json");
    }

    public function testDiffDifferentFileTypes()
    {
        self::checkDiff("before.json", "after.yaml", "before_after.diff.pretty");
    }
}
