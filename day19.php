<?php

use \Ds\Map;

$filename = 'inputs/day19.txt';
$lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$inventory = explode(', ', $lines[0]);
$patterns = array_slice($lines, 1);

function dpPart1(array &$inventory, Map &$cache, string $pattern): bool
{
    if ($cache->hasKey($pattern)) {
        return $cache[$pattern];
    }

    if (in_array($pattern, $inventory)) {
        return $cache[$pattern] = true;
    }

    foreach ($inventory as $basePattern) {
        if (str_starts_with($pattern, $basePattern) and dpPart1($inventory, $cache, substr($pattern, strlen($basePattern)))) {
            return $cache[$pattern] = true;
        }
    }

    return $cache[$pattern] = false;
}

function dpPart2(array &$inventory, Map &$cache, string $pattern): int
{
    if ($cache->hasKey($pattern)) {
        return $cache[$pattern];
    }

    $ways = 0;

    if (in_array($pattern, $inventory)) {
        $ways++;
    }

    foreach ($inventory as $basePattern) {
        if (str_starts_with($pattern, $basePattern)) {
            $ways += dpPart2($inventory, $cache, substr($pattern, strlen($basePattern)));
        }
    }

    return $cache[$pattern] = $ways;
}

$part1 = 0;
$part2 = 0;
$cacheP1 = new Map();
$cacheP2 = new Map();

foreach ($patterns as $pattern) {
    $part1 += dpPart1($inventory, $cacheP1, $pattern);
    $part2 += dpPart2($inventory, $cacheP2, $pattern);
}

echo "$part1\n$part2\n";
