<?php

namespace Differ\formatters;

use function Differ\formatters\pretty\format as prettyFormat;
use function Differ\formatters\plain\format as plainFormat;
use function Differ\formatters\json\format as jsonFormat;

function format($diffTree, $format = 'pretty')
{
    switch ($format) {
        case 'pretty':
            return prettyFormat($diffTree);
        case 'plain':
            return plainFormat($diffTree);
        case 'json':
            return jsonFormat($diffTree);
        default:
            throw new \Exception("Unknown format '$format'");
    }
}
