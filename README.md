[![Build Status](https://travis-ci.org/AlexP11223/php-project-lvl2.svg?branch=master)](https://travis-ci.org/AlexP11223/php-project-lvl2)
[![Maintainability](https://api.codeclimate.com/v1/badges/beae01838b1d702842ac/maintainability)](https://codeclimate.com/github/AlexP11223/php-project-lvl2/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/beae01838b1d702842ac/test_coverage)](https://codeclimate.com/github/AlexP11223/php-project-lvl2/test_coverage)

# gendiff

Compares two JSON, YAML files and shows the changes between them in different formats: `pretty`, `plain`, `json`. 

## Installation

- Install PHP 7.2+ and [Composer](https://getcomposer.org/doc/00-intro.md#globally).
- Run `composer global require alexp11223/gendiff`.
- Make sure that the Composer global bin dir (`composer global config bin-dir --absolute`) is in your `PATH`.

[![asciicast](https://asciinema.org/a/9qTbJk0qjlNH8fAXwii5nhB9Z.svg)](https://asciinema.org/a/9qTbJk0qjlNH8fAXwii5nhB9Z)

## Usage

```
gendiff [--format <fmt>] <firstFile> <secondFile>
```

Examples:

```
gendiff before.json after.json
gendiff before.yaml after.yaml
gendiff before.json after.yaml
gendiff before.json after.json --format plain
gendiff before.json after.json --format json
```

### pretty

Human-readable JSON-like format similar to `diff`.

Flat files:

[![asciicast](https://asciinema.org/a/w1GQYjwBYf8Rw1rCTxosWHG7a.svg)](https://asciinema.org/a/w1GQYjwBYf8Rw1rCTxosWHG7a)

Nested objects:

[![asciicast](https://asciinema.org/a/cIOkMEdDKqhijQ6f0WuoAWNS0.svg)](https://asciinema.org/a/cIOkMEdDKqhijQ6f0WuoAWNS0)

### plain

Textual description of the changes.

[![asciicast](https://asciinema.org/a/jG4WCuuqGt4TEBaFOikZWb5e9.svg)](https://asciinema.org/a/jG4WCuuqGt4TEBaFOikZWb5e9)

### json

JSON tree describing the changes, intended for automated processing.

 [![asciicast](https://asciinema.org/a/WsoXeiMW6AGnaS7cgNfVua4ZG.svg)](https://asciinema.org/a/WsoXeiMW6AGnaS7cgNfVua4ZG)
