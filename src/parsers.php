<?php

namespace Differ\parsers;

use Symfony\Component\Yaml\Yaml;

function parseJson($text)
{
    return json_decode($text);
}

function parseYaml($text)
{
    return Yaml::parse($text, Yaml::PARSE_OBJECT_FOR_MAP);
}

function load($filePath)
{
    $ext = pathinfo($filePath, PATHINFO_EXTENSION);
    $content = file_get_contents($filePath);
    switch ($ext) {
        case 'json':
            return parseJson($content);
        case 'yaml':
        case 'yml':
            return parseYaml($content);
        default:
            throw new \Exception("Unknown type $ext");
    }
}
