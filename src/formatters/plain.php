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
    $currentParents = $name ? array_merge($parents, [$name]) : $parents;

    $children = $node['children'];
    if (!empty($children)) {
        $results = array_map(function ($child) use ($currentParents) {
            return getChanges($child, $currentParents);
        }, $children);
        return array_merge(...$results);
    }

    switch ($node['type']) {
        case TYPE_PROPERTY:
            $path = implode('.', $currentParents);
            switch ($node['state']) {
                case ADDED:
                    $formattedValue = formatValue($node['newValue']);
                    return ["Added property '$path' with value '$formattedValue'"];
                case REMOVED:
                    $formattedValue = formatValue($node['oldValue']);
                    return ["Removed property '$path' with value '$formattedValue'"];
                case CHANGED:
                    $formattedOld = formatValue($node['oldValue']);
                    $formattedNew = formatValue($node['newValue']);
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
