<?php

namespace Differ\formatters\pretty;

use const Differ\ADDED;
use const Differ\NESTED;
use const Differ\REMOVED;
use const Differ\CHANGED;
use const Differ\UNCHANGED;

const TYPE_OBJECT = 'object';
const TYPE_PROPERTY = 'property';

function stateToName($state)
{
    return strtoupper("_${state}_");
}

function getValueType($node)
{
    if (isset($node['name'])) {
        return TYPE_PROPERTY;
    }
    return TYPE_OBJECT;
}

function removeQuotes($json)
{
    return preg_replace('/"(.*?)"/', '$1', $json);
}

function removeCommas($json)
{
    return preg_replace('/,$/m', '', $json);
}

function prettifyJson($json)
{
    return removeCommas(removeQuotes($json));
}

function formatValue($value)
{
    $jsonText = json_encode($value, JSON_PRETTY_PRINT);
    return json_encode($value);
}

function traverse($node)
{
    $valueType = getValueType($node);
    switch ($valueType) {
        case TYPE_OBJECT:
            switch ($node['state']) {
                case UNCHANGED:
                    return $node['oldValue'];
                case NESTED:
                    $properties = array_merge(...array_map(function ($property) {
                        return traverse($property);
                    }, $node['children']));
                    return (object)$properties;
            }
            throw new \Exception("Unsupported OBJECT state ${node['state']}");
        case TYPE_PROPERTY:
            $name = $node['name'];
            switch ($node['state']) {
                case UNCHANGED:
                    return [$name => $node['oldValue']];
                case ADDED:
                    return [stateToName($node['state']) . $name => $node['newValue']];
                case REMOVED:
                    return [stateToName($node['state']) . $name => $node['oldValue']];
                case CHANGED:
                    return [
                        stateToName(REMOVED) . $name => $node['oldValue'],
                        stateToName(ADDED) . $name => $node['newValue']
                    ];
                case NESTED:
                    return [$name => traverse($node['children'][0])];
            }
            throw new \Exception("Unsupported PROPERTY state ${node['state']}");
    }
    throw new \Exception("Unsupported type ${node['type']}");
}

function format($diffTree)
{
    $json = traverse($diffTree[0]); // TODO: refactor

    $jsonText = prettifyJson(json_encode($json, JSON_PRETTY_PRINT));
    $diffText = str_replace(
        [
            '  ' . stateToName(ADDED),
            '  ' . stateToName(REMOVED)],
        [
            '+ ',
            '- '
        ],
        $jsonText
    );
    return $diffText;
}
