<?php

namespace Differ;

use function Differ\parsers\load;
use function Differ\utils\get_object_keys;

const ADDED = 'added';
const REMOVED = 'removed';
const CHANGED = 'changed';
const UNCHANGED = 'unchanged';

const TYPE_OBJECT = 'object';
const TYPE_PROPERTY = 'property';
// TODO: add array support?

function makeNode($objectType, $state, $value, $children = [], $fields = [])
{
    return array_merge([
        'type' => $objectType,
        'state' => $state,
        'value' => $value,
        'children' => $children
    ], $fields);
}

function makeObjectNode($state, $value, $properties = [])
{
    return makeNode(TYPE_OBJECT, $state, $value, $properties);
}

function makePropertyNode($state, $name, $value, $children = [])
{
    return makeNode(TYPE_PROPERTY, $state, $value, $children, ['name' => $name]);
}

function diff($firstObj, $secondObj)
{
    $keys = array_unique(array_merge(get_object_keys($firstObj), get_object_keys($secondObj)));

    $properties = array_map(function ($key) use ($firstObj, $secondObj) {
        if (!property_exists($secondObj, $key)) {
            return makePropertyNode(REMOVED, $key, $firstObj->$key);
        }
        if (!property_exists($firstObj, $key)) {
            return makePropertyNode(ADDED, $key, $secondObj->$key);
        }
        if ($firstObj->$key !== $secondObj->$key) {
            $old = $firstObj->$key;
            $new = $secondObj->$key;
            if (is_object($old) && is_object($new)) {
                return makePropertyNode(CHANGED, $key, null, [diff($old, $new)]);
            }
            return makePropertyNode(CHANGED, $key, [$old, $new], []);
        }
        return makePropertyNode(UNCHANGED, $key, $firstObj->$key);
    }, $keys);

    $isUnchanged = empty(array_filter($properties, function ($item) {
        return $item['state'] != UNCHANGED;
    }));

    return makeObjectNode(
        $isUnchanged ? UNCHANGED : CHANGED,
        $isUnchanged ? $firstObj : null,
        $properties
    );
}

function stateToName($state)
{
    return strtoupper("_${state}_");
}

function render($diff)
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

function genDiff($firstObj, $secondObj)
{
    $diff = diff($firstObj, $secondObj);
    return render($diff);
}

function genDiffForFiles($firstFilePath, $secondFilePath): string
{
    return genDiff(load($firstFilePath), load($secondFilePath));
}
