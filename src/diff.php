<?php

namespace Differ;

use function Differ\formatters\format;
use function Differ\parsers\parse;
use function Differ\utils\get_object_keys;

const ADDED = 'added';
const REMOVED = 'removed';
const CHANGED = 'changed';
const NESTED_CHANGED = 'nested_changed';
const UNCHANGED = 'unchanged';

// TODO: add array support?

function makeNode($state, $oldValue, $newValue, $children = [], $fields = [])
{
    return array_merge([
        'state' => $state,
        'oldValue' => $oldValue,
        'newValue' => $newValue,
        'children' => array_values($children)
    ], $fields);
}

function makeObjectNode($state, $oldValue, $newValue, $properties = [])
{
    return makeNode($state, $oldValue, $newValue, $properties);
}

function makePropertyNode($state, $name, $oldValue, $newValue, $children = [])
{
    return makeNode($state, $oldValue, $newValue, $children, ['name' => $name]);
}

function makeDiffTree($firstObj, $secondObj)
{
    $keys = array_unique(array_merge(get_object_keys($firstObj), get_object_keys($secondObj)));

    $properties = array_map(function ($key) use ($firstObj, $secondObj) {
        if (!property_exists($secondObj, $key)) {
            return makePropertyNode(REMOVED, $key, $firstObj->$key, null);
        }
        if (!property_exists($firstObj, $key)) {
            return makePropertyNode(ADDED, $key, null, $secondObj->$key);
        }

        $old = $firstObj->$key;
        $new = $secondObj->$key;
        // primitive values should be compared using ===, but for objects it will not work
        if (is_object($old) && is_object($new)) {
            if ($old != $new) {
                return makePropertyNode(NESTED_CHANGED, $key, $old, $new, [makeDiffTree($old, $new)]);
            }
        } else {
            if ($old !== $new) {
                return makePropertyNode(CHANGED, $key, $old, $new);
            }
        }

        return makePropertyNode(UNCHANGED, $key, $firstObj->$key, $firstObj->$key);
    }, $keys);

    $hasChanges = !empty(array_filter($properties, function ($item) {
        return $item['state'] != UNCHANGED;
    }));
    if ($hasChanges) {
        return makeObjectNode(NESTED_CHANGED, $firstObj, $secondObj, $properties);
    }
    return makeObjectNode(UNCHANGED, $firstObj, $secondObj);
}

function genDiff($firstObj, $secondObj, $format = 'pretty')
{
    $diffTree = makeDiffTree($firstObj, $secondObj);
    return format($diffTree, $format);
}

function load($filePath)
{
    $content = file_get_contents($filePath);
    return parse($content, pathinfo($filePath, PATHINFO_EXTENSION));
}

function genDiffForFiles($firstFilePath, $secondFilePath, $format = 'pretty'): string
{
    return genDiff(load($firstFilePath), load($secondFilePath), $format);
}
