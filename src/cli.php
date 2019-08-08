<?php

namespace Gendiff\cli;

function run(array $argv)
{
    $doc = <<<DOC
Generate diff

Usage:
  gendiff (-h|--help)
  gendiff (-v|--version)

Options:
  -h --help                     Show this screen
  -v --version                  Show version

DOC;

    $args = \Docopt::handle($doc, ['argv' => $argv]);
}