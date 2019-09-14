<?php

namespace Differ;

use function Differ\formatters\format;
use function Differ\parsers\load;
use function Differ\utils\get_object_keys;

const ADDED = 'added';
const REMOVED = 'removed';
const CHANGED = 'changed';
const UNCHANGED = 'unchanged';

const TYPE_OBJECT = 'object';
const TYPE_PROPERTY = 'property';
// TODO: add array support?

function makeNode($objectType, $state, $oldValue, $newValue, $children = [], $fields = [])
{
    return array_merge([
        'type' => $objectType,
        'state' => $state,
        'oldValue' => $oldValue,
        'newValue' => $newValue,
        'children' => array_values($children)
    ], $fields);
}

function makeObjectNode($state, $oldValue, $newValue, $properties = [])
{
    return makeNode(TYPE_OBJECT, $state, $oldValue, $newValue, $properties);
}

function makePropertyNode($state, $name, $oldValue, $newValue, $children = [])
{
    return makeNode(TYPE_PROPERTY, $state, $oldValue, $newValue, $children, ['name' => $name]);
}

function diff($firstObj, $secondObj)
{
    $keys = array_unique(array_merge(get_object_keys($firstObj), get_object_keys($secondObj)));

    $properties = array_map(function ($key) use ($firstObj, $secondObj) {
        if (!property_exists($secondObj, $key)) {
            return makePropertyNode(REMOVED, $key, $firstObj->$key, null);
        }
        if (!property_exists($firstObj, $key)) {
            return makePropertyNode(ADDED, $key, null, $secondObj->$key);
        }
        if ($firstObj->$key !== $secondObj->$key) {
            $old = $firstObj->$key;
            $new = $secondObj->$key;
            if (is_object($old) && is_object($new)) {
                return makePropertyNode(CHANGED, $key, $old, $new, [diff($old, $new)]);
            }
            return makePropertyNode(CHANGED, $key, $old, $new, []);
        }
        return makePropertyNode(UNCHANGED, $key, $firstObj->$key, $firstObj->$key);
    }, $keys);

    $isUnchanged = empty(array_filter($properties, function ($item) {
        return $item['state'] != UNCHANGED;
    }));

    return makeObjectNode($isUnchanged ? UNCHANGED : CHANGED, $firstObj, $secondObj, $properties);
}

function genDiff($firstObj, $secondObj, $format = 'pretty')
{
    $diff = diff($firstObj, $secondObj);
    return format($diff, $format);
}

function genDiffForFiles($firstFilePath, $secondFilePath, $format = 'pretty'): string
{
    return genDiff(load($firstFilePath), load($secondFilePath), $format);
}
