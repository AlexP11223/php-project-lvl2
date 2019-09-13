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

function getChanges($node, $parents = [])
{
    $name = $node['name'] ?? null;

    $children = $node['children'];
    if (!empty($children)) {
        $newParents = $name ? array_merge($parents, [$name]) : $parents;

        $results = array_map(function ($child) use ($newParents) {
            return getChanges($child, $newParents);
        }, $children);
        return array_merge(...$results);
    }

    switch ($node['type']) {
        case TYPE_PROPERTY:
            $value = $node['value'];
            $path = implode('.', array_merge($parents, [$name]));
            switch ($node['state']) {
                case ADDED:
                    $formattedValue = formatValue($value);
                    return ["Added property '$path' with value '$formattedValue'"];
                case REMOVED:
                    $formattedValue = formatValue($value);
                    return ["Removed property '$path' with value '$formattedValue'"];
                case CHANGED:
                    [$old, $new] = $value;
                    $formattedOld = formatValue($old);
                    $formattedNew = formatValue($new);
                    return ["Changed property '$path' from '$formattedOld' to '$formattedNew'"];
            }
    }

    return [];
}

function format($diff)
{
    $changes = getChanges($diff);

    return implode(PHP_EOL, $changes);
}
