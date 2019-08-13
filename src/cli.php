<?php

namespace Differ\cli;

use function Differ\genDiffForFiles;

function run()
{
    $doc = <<<DOC
Generate diff

Usage:
  gendiff (-h|--help)
  gendiff (-v|--version)
  gendiff [--format <fmt>] <firstFile> <secondFile>

Options:
  -h --help                     Show this screen
  -v --version                  Show version
  --format <fmt>                Report format [default: pretty]

DOC;

    $args = \Docopt::handle($doc);

    $firstFilePath = $args['<firstFile>'];
    $secondFilePath = $args['<secondFile>'];

    echo genDiffForFiles($firstFilePath, $secondFilePath) . PHP_EOL;
}
