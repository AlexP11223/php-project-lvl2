<?php

namespace Differ\formatters;

use function Differ\formatters\pretty\format as prettyFormat;

function format($diff, $format = 'pretty')
{
    switch ($format) {
        case 'pretty':
            return prettyFormat($diff);
        default:
            throw new \Exception("Unknown format '$format'");
    }
}
