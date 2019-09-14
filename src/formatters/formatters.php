<?php

namespace Differ\formatters;

use function Differ\formatters\pretty\format as prettyFormat;
use function Differ\formatters\plain\format as plainFormat;
use function Differ\formatters\json\format as jsonFormat;

function format($diff, $format = 'pretty')
{
    switch ($format) {
        case 'pretty':
            return prettyFormat($diff);
        case 'plain':
            return plainFormat($diff);
        case 'json':
            return jsonFormat($diff);
        default:
            throw new \Exception("Unknown format '$format'");
    }
}
