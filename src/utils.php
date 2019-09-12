<?php

namespace Differ\utils;

function get_object_keys($obj)
{
    return array_keys(get_object_vars($obj));
}
