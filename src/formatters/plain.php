<?php

namespace Differ\formatters\plain;

use function Funct\Collection\flatten;
use const Differ\ADDED;
use const Differ\REMOVED;
use const Differ\CHANGED;
use const Differ\NESTED;

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
    $path = implode('.', $currentParents);

    switch ($node['state']) {
        case NESTED:
            $results = flatten(array_map(function ($child) use ($currentParents) {
                return getChanges($child, $currentParents);
            }, $node['children']));
            return array_filter($results);
        case ADDED:
            $formattedValue = formatValue($node['newValue']);
            return "Added property '$path' with value '$formattedValue'";
        case REMOVED:
            $formattedValue = formatValue($node['oldValue']);
            return "Removed property '$path' with value '$formattedValue'";
        case CHANGED:
            $formattedOld = formatValue($node['oldValue']);
            $formattedNew = formatValue($node['newValue']);
            return "Changed property '$path' from '$formattedOld' to '$formattedNew'";
    }

    return null;
}

function format($diffTree)
{
    $changes = getChanges($diffTree);

    return implode(PHP_EOL, $changes);
}
