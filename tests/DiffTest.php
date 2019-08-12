<?php

namespace Differ;

use PHPUnit\Framework\TestCase;

class DiffTest extends TestCase
{
    private static function loadFixtureFile($filePath)
    {
        $fullFilePath = dirname(__FILE__) . '/fixtures/' . $filePath;
        return file_get_contents($fullFilePath);
    }

    private static function checkDiff($filePath1, $filePath2, $expectedDiffFilePath)
    {
        $expectedDiff = trim(self::loadFixtureFile($expectedDiffFilePath));
        $diff = genDiff(self::loadFixtureFile($filePath1), self::loadFixtureFile($filePath2));
        self::assertEquals($expectedDiff, $diff);
    }

    public function testNormal()
    {
        self::checkDiff('1.json', '2.json', '1_2.diff');
        self::checkDiff('2.json', '1.json', '2_1.diff');
    }

    public function testSame()
    {
        self::checkDiff('1.json', '1.json', '1_1.diff');
    }
}
