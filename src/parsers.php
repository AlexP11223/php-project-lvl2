<?php

namespace Differ\parsers;

function parseJson($text)
{
    return json_decode($text, true);
}

function load($filePath)
{
    $ext = pathinfo($filePath, PATHINFO_EXTENSION);
    $content = file_get_contents($filePath);
    switch ($ext) {
        case 'json':
            return parseJson($content);
        default:
            throw new \Exception("Unknown type $ext");
    }
}
