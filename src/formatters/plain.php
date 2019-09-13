<?php

namespace Differ\formatters\plain;

use const Differ\ADDED;
use const Differ\REMOVED;
use const Differ\CHANGED;
use const Differ\TYPE_PROPERTY;

function formatValue($value)
{
    if (is_object($value)) {
        return '<complex value>';
    }
    $str = json_encode($value);
    $strWithoutQuotes = preg_replace('/"(.*?)"/', '$1', $str);
    return $strWithoutQuotes;
}

function getChanges($node)
{
    $children = $node['children'];
    if (!empty($children)) {
        $results = array_map(function ($child) {
            return getChanges($child);
        }, $children);
        return array_merge(...$results);
    }

    switch ($node['type']) {
        case TYPE_PROPERTY:
            ['name' => $name, 'value' => $value] = $node;
            switch ($node['state']) {
                case ADDED:
                    $formattedValue = formatValue($value);
                    return ["Added property '$name' with value '$formattedValue'"];
                case REMOVED:
                    $formattedValue = formatValue($value);
                    return ["Removed property '$name' with value '$formattedValue'"];
                case CHANGED:
                    [$old, $new] = $value;
                    $formattedOld = formatValue($old);
                    $formattedNew = formatValue($new);
                    return ["Changed property '$name' from '$formattedOld' to '$formattedNew'"];
            }
    }

    return [];
}

function format($diff)
{
    $changes = getChanges($diff);

    return implode(PHP_EOL, $changes);
}
