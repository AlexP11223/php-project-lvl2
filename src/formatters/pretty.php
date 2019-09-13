<?php

namespace Differ\formatters\pretty;

use const Differ\ADDED;
use const Differ\REMOVED;
use const Differ\CHANGED;
use const Differ\UNCHANGED;
use const Differ\TYPE_OBJECT;
use const Differ\TYPE_PROPERTY;

function stateToName($state)
{
    return strtoupper("_${state}_");
}

function format($diff)
{
    $traverse = function ($node) use (&$traverse) {
        if (!is_array($node)) {
            return $node;
        }
        switch ($node['type']) {
            case TYPE_OBJECT:
                switch ($node['state']) {
                    case UNCHANGED:
                        return $node['value'];
                    case CHANGED:
                        $properties =  array_merge(...array_map(function ($property) use ($traverse) {
                            return $traverse($property);
                        }, $node['children']));
                        return (object)$properties;
                }
                throw new \Exception("Unsupported OBJECT state ${node['state']}");
            case TYPE_PROPERTY:
                $name = $node['name'];
                $value = empty($node['children']) ? $node['value'] : $node['children'][0];
                switch ($node['state']) {
                    case UNCHANGED:
                        return [$name => $node['value']];
                    case ADDED:
                    case REMOVED:
                        return [stateToName($node['state']) . $name => $traverse($value)];
                    case CHANGED:
                        if (empty($node['children'])) {
                            [$old, $new] = $value;
                            return [
                                stateToName(REMOVED) . $name => $traverse($old),
                                stateToName(ADDED) . $name => $traverse($new)
                            ];
                        }
                        return [$name => $traverse($value)];
                }
                throw new \Exception("Unsupported PROPERTY state ${node['state']}");
        }
        throw new \Exception("Unsupported type ${node['type']}");
    };

    $json = $traverse($diff);

    $jsonText = json_encode($json, JSON_PRETTY_PRINT);
    $jsonTextWithoutQuotes = preg_replace('/"(.+?)"/', '$1', $jsonText);
    $jsonTextWithoutQuotesAndCommas = preg_replace('/,$/m', '', $jsonTextWithoutQuotes);
    $diffText = str_replace(
        [
            '  ' . stateToName(ADDED),
            '  ' . stateToName(REMOVED)],
        [
            '+ ',
            '- '
        ],
        $jsonTextWithoutQuotesAndCommas
    );
    return $diffText;
}
