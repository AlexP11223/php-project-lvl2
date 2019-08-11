<?php

namespace Differ;

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

function genDiff($firstFileContent, $secondFileContent): string
{
    $firstObj = json_decode($firstFileContent, true);
    $secondObj = json_decode($secondFileContent, true);

    $keys = array_keys(array_merge($firstObj, $secondObj));

    $result = array_map(function ($key) use ($firstObj, $secondObj) {
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

    $simplifiedResult = array_reduce($result, function ($acc, $it) {
        [$key, $value, $state] = $it;

        switch ($state) {
            case CHANGED:
                [$old, $new] = $value;
                $acc[] = [$key, $old, REMOVED];
                $acc[] = [$key, $new, ADDED];
                break;
            default:
                $acc[] = $it;
                break;
        }

        return $acc;
    }, []);

    $lines = array_map(function ($it) {
        [$key, $value, $state] = $it;
        $encodedValue = json_encode($value);
        $stateChar = stateChar($state);
        return "  $stateChar $key: $encodedValue";
    }, $simplifiedResult);

    return implode(PHP_EOL, array_merge(['{'], $lines, ['}']));
}
