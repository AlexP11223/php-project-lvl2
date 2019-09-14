<?php

namespace Differ\formatters\json;

function format($diff)
{
    // TODO: maybe should simplify the output?
    // though I think it makes sense to just output the intermediate structure produced by our diff because
    // it supposed to be the most convenient for automated processing (that's why we create it)
    return json_encode($diff, JSON_PRETTY_PRINT);
}
