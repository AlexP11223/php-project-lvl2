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

function parse($content, $format)
{
    switch ($format) {
        case 'json':
            return parseJson($content);
        case 'yaml':
        case 'yml':
            return parseYaml($content);
        default:
            throw new \Exception("Unknown format $format");
    }
}
