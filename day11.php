<?php

use \Ds\Map;

$filename = 'inputs/day11.txt';
$lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

function dp(array &$cache, int $curr, int $depth, int $depthLimit): int
{
    if (!$cache[$depth]->hasKey($curr)) {
        if ($depth == $depthLimit) {
            $cache[$depth]->put($curr, 1);
        } elseif ($curr == 0) {
            $cache[$depth]->put($curr, dp($cache, 1, $depth + 1, $depthLimit));
        } elseif (strlen(strval($curr)) % 2 == 0) {
            list($first, $second) = str_split(strval($curr), intdiv(strlen(strval($curr)), 2));
            $cache[$depth]->put($curr, dp($cache, intval($first), $depth + 1, $depthLimit) + dp($cache, intval($second), $depth + 1, $depthLimit));
        } else {
            $cache[$depth]->put($curr, dp($cache, $curr * 2024, $depth + 1, $depthLimit));
        }
    }

    return $cache[$depth]->get($curr);
}

function solve(string $line, int $depthLimit): int
{
    $ans = 0;

    // ! DO NOT USE array_fill FOR OBJECTS
    $cache = [];
    for ($i = 0; $i <= $depthLimit; $i++) {
        $cache[] = new Map();
    }

    foreach (array_map('intval', explode(' ', $line)) as $num) {
        $ans += dp($cache, $num, 0, $depthLimit);
    }

    return $ans;
}

echo solve($lines[0], 25) . "\n";
echo solve($lines[0], 75) . "\n";
