<?php

namespace Differ\formatters\pretty;

use function Funct\Collection\flatten;
use const Differ\ADDED;
use const Differ\NESTED;
use const Differ\REMOVED;
use const Differ\CHANGED;
use const Differ\UNCHANGED;

const INDENT_SIZE = 4;

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

function stateToChar($state)
{
    switch ($state) {
        case ADDED:
            return '+';
        case REMOVED:
            return '-';
        case UNCHANGED:
            return ' ';
    }
    throw new \Exception("Unsupported state $state");
}

function indent($level, $state = UNCHANGED, $size = INDENT_SIZE)
{
    if ($level === 0) {
        return '';
    }
    if ($level < 0) {
        throw new \Exception("Indent level cannot be less than 0, $level given");
    }
    if ($size < 2) {
        throw new \Exception("Indent size cannot be less than 2, $size given");
    }
    return str_repeat(' ', $level * $size - 2) . stateToChar($state) . ' ';
}

function formatValue($value, $level)
{
    $json = json_encode($value, JSON_PRETTY_PRINT);
    $prettyJson = prettifyJson($json);
    $lines = explode(PHP_EOL, $prettyJson);
    $indentedLines = flatten([ // no ... here until 7.4 :(
        $lines[0],
        array_map(function ($line) use ($level) {
            return indent($level) . $line;
        }, array_slice($lines, 1))
    ]);
    return implode(PHP_EOL, $indentedLines);
}

function traverse($nodes, $level = 0)
{
    $lines = array_map(function ($node) use ($level) {
        $name = $node['name'] ?? null;
        switch ($node['state']) {
            case UNCHANGED:
                return indent($level) . "$name: " . formatValue($node['oldValue'], $level);
            case ADDED:
                return indent($level, ADDED) . "$name: " . formatValue($node['newValue'], $level);
            case REMOVED:
                return indent($level, REMOVED) . "$name: " . formatValue($node['oldValue'], $level);
            case CHANGED:
                return indent($level, REMOVED) . "$name: " . formatValue($node['oldValue'], $level) . PHP_EOL .
                    indent($level, ADDED) . "$name: " . formatValue($node['newValue'], $level);
            case NESTED:
                return indent($level) . "$name: {" . PHP_EOL .
                    traverse($node['children'], $level + 1) . PHP_EOL .
                    indent($level) . '}';
        }
        throw new \Exception("Unsupported state ${node['state']}");
    }, $nodes);

    return implode(PHP_EOL, $lines);
}

function format($diffTree)
{
    return '{' . PHP_EOL . traverse($diffTree, 1) . PHP_EOL . '}';
}
