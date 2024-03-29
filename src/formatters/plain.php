<?php

namespace Differ\formatters\plain;

use function Funct\Collection\flattenAll;
use const Differ\ADDED;
use const Differ\REMOVED;
use const Differ\CHANGED;

function formatValue($value)
{
    if (is_object($value)) {
        return '<complex value>';
    }
    $str = json_encode($value);
    $strWithoutQuotes = preg_replace('/"(.*?)"/', '$1', $str);
    return $strWithoutQuotes;
}

function traverse($nodes, $parents = [])
{
    return array_map(function ($node) use ($parents) {
        $name = $node['name'] ?? null;
        $currentParents = $name ? array_merge($parents, [$name]) : $parents;
        $path = implode('.', $currentParents);

        switch ($node['state']) {
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

        return traverse($node['children'], $currentParents);
    }, $nodes);
}

function getChanges($diffTree)
{
    return array_filter(flattenAll(traverse($diffTree)));
}

function format($diffTree)
{
    $changes = getChanges($diffTree);

    return implode(PHP_EOL, $changes);
}
