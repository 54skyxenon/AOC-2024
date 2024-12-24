# Advent of Code 2024

I'm back for AoC 2024, this time in PHP!

## Reflections

Went well generally speaking! I can see why people think PHP is a meme language, though there were some very useful functions I liked such as `var_dump` and `str_replace`.

I personally struggled with the Part 2 of the following days:
- Day 14
- Day 17
- Day 21
- Day 24

## Input
All input is read from TXT files under the `inputs/` folder that you need to make prior to executing any code.

## Environment Details
My PHP version:
```bash
$ php -v
PHP 8.4.1 (cli) (built: Nov 20 2024 09:48:35) (NTS)
Copyright (c) The PHP Group
Zend Engine v4.4.1, Copyright (c) Zend Technologies
    with Zend OPcache v8.4.1, Copyright (c), by Zend Technologies
```

To install the data structures extension:
```bash
$ pecl install ds
```

I use Composer to install 3rd-party libraries. Follow [these steps](https://getcomposer.org/download/) to install Composer if you haven't already. Assuming a local installation, run:
```
$ php composer.phar install
```

To execute a specific PHP file I do:
```bash
$ php hello.php
Hello, World!
```
