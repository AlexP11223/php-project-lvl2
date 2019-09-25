<?php

namespace Differ\formatters\json;

function getJsonRoot($diffTree)
{
    if (count($diffTree) === 1) {
        return $diffTree[0];
    }
    return $diffTree;
}

function format($diffTree)
{
    // TODO: maybe should simplify the output?
    // though I think it makes sense to just output the intermediate structure produced by our diff because
    // it supposed to be the most convenient for automated processing (that's why we create it)
    return json_encode(getJsonRoot($diffTree), JSON_PRETTY_PRINT);
}
