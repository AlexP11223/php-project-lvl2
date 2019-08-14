<?php

namespace Differ;

use function Differ\parsers\load;
use function Funct\Collection\flatten;

const ADDED = 'added';
const REMOVED = 'removed';
const CHANGED = 'changed';
const UNCHANGED = 'unchanged';

function stateChar($state)
{
    switch ($state) {
        case ADDED:
            return '+';
        case REMOVED:
            return '-';
        case UNCHANGED:
            return ' ';
        default:
            throw new \Exception("Unsupported state $state");
    }
}

function diff(array $firstObj, array $secondObj)
{
    $keys = array_keys(array_merge($firstObj, $secondObj));

    return array_map(function ($key) use ($firstObj, $secondObj) {
        if (!array_key_exists($key, $secondObj)) {
            return [$key, $firstObj[$key], REMOVED];
        }
        if (!array_key_exists($key, $firstObj)) {
            return [$key, $secondObj[$key], ADDED];
        }
        if ($firstObj[$key] !== $secondObj[$key]) {
            return [$key, [$firstObj[$key], $secondObj[$key]], CHANGED];
        }
        return [$key, $firstObj[$key], UNCHANGED];
    }, $keys);
}

function genDiff(array $firstObj, array $secondObj)
{
    $diff = diff($firstObj, $secondObj);

    $simplifiedDiff = flatten(array_map(function ($it) {
        [$key, $value, $state] = $it;

        switch ($state) {
            case CHANGED:
                [$old, $new] = $value;
                return [
                    [$key, $old, REMOVED],
                    [$key, $new, ADDED]
                ];
            default:
                return [$it];
        }
    }, $diff));

    $lines = array_map(function ($it) {
        [$key, $value, $state] = $it;
        $encodedValue = json_encode($value);
        $stateChar = stateChar($state);
        return "  $stateChar $key: $encodedValue";
    }, $simplifiedDiff);

    return implode(PHP_EOL, array_merge(['{'], $lines, ['}']));
}

function genDiffForFiles($firstFilePath, $secondFilePath): string
{
    return genDiff(load($firstFilePath), load($secondFilePath));
}
