<?php

/** Day 21 Part 2 solution: https://www.youtube.com/watch?v=q5I6ZvJmHEo&ab_channel=WilliamY.Feng */

use \Ds\Map;

$filename = 'inputs/day21.txt';
$lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$NUMERIC_PAD_LOCATIONS = [
    '7' => [0, 0],
    '8' => [0, 1],
    '9' => [0, 2],
    '4' => [1, 0],
    '5' => [1, 1],
    '6' => [1, 2],
    '1' => [2, 0],
    '2' => [2, 1],
    '3' => [2, 2],
    '0' => [3, 1],
    'A' => [3, 2],
];

$ARROW_PAD_LOCATIONS = [
    '^' => [0, 1],
    'A' => [0, 2],
    '<' => [1, 0],
    'v' => [1, 1],
    '>' => [1, 2],
];

$MOVEMENTS = [
    '<' => [0, -1],
    '>' => [0, 1],
    '^' => [-1, 0],
    'v' => [1, 0],
];

// Mimics itertools.combinations(range(rangeEndExclusive), r=choose)
function combinations(int $rangeEndExclusive, int $choose): array
{
    $indices = [];

    for ($mask = 0; $mask < 2 ** $rangeEndExclusive; $mask++) {
        $maskBinary = strrev(decbin($mask));

        if (substr_count($maskBinary, '1') === $choose) {
            $indicesHere = [];

            for ($i = 0; $i < strlen($maskBinary); $i++) {
                if ($maskBinary[$i] === '1') {
                    $indicesHere[] = $i;
                }
            }

            $indices[] = $indicesHere;
        }
    }

    return $indices;
}

// ! This DP works because the rest of the layers are all lined up to be AAAAAA.....
function transform(Map &$cache, string $current, string $target, int $depth, bool $onNumericKeypad): int
{
    global $ARROW_PAD_LOCATIONS, $NUMERIC_PAD_LOCATIONS, $MOVEMENTS;

    $state = "$current-$target-$depth-$onNumericKeypad";
    if ($cache->hasKey($state)) {
        return $cache[$state];
    }

    $keypad = $onNumericKeypad ? $NUMERIC_PAD_LOCATIONS : $ARROW_PAD_LOCATIONS;
    $distR = $keypad[$target][0] - $keypad[$current][0];
    $distC = $keypad[$target][1] - $keypad[$current][1];
    $distTotal = abs($distR) + abs($distC);
    $keyR = ($distR >= 0) ? 'v' : '^';
    $keyC = ($distC >= 0) ? '>' : '<';

    // Build move sequences from current key to target key that don't cause the robot to fall off
    $validSequences = [];
    foreach (combinations($distTotal, abs($distR)) as $combination) {
        // Example: ^>><v
        $sequence = array_fill(0, $distTotal, $keyC);
        foreach ($combination as $index) {
            $sequence[$index] = $keyR;
        }

        $valid = true;
        list($currR, $currC) = $keypad[$current];

        foreach ($sequence as $move) {
            list($dr, $dc) = $MOVEMENTS[$move];
            $currR += $dr;
            $currC += $dc;

            if (!in_array([$currR, $currC], $keypad)) {
                $valid = false;
                break;
            }
        }

        if ($valid) {
            $validSequences[] = $sequence;
        }
    }

    $best = INF;

    // Check each one with the next depth
    foreach ($validSequences as $sequence) {
        $sequence = ['A', ...$sequence, 'A'];

        // We're on the last arrow keypad whose robot we can directly control
        if ($depth === 0) {
            return $cache[$state] = (count($sequence) - 1);
        }

        $bestSequence = 0;
        for ($i = 0; $i < count($sequence) - 1; $i++) {
            $bestSequence += transform($cache, $sequence[$i], $sequence[$i + 1], $depth - 1, false);
        }

        $best = min($best, $bestSequence);
    }

    return $cache[$state] = $best;
}

function solve(array &$lines, int $depthLimit)
{
    $ans = 0;
    $cache = new Map();

    foreach ($lines as $code) {
        $code = 'A' . $code;
        $shortest = 0;
        for ($i = 0; $i < strlen($code) - 1; $i++) {
            $shortest += transform($cache, $code[$i], $code[$i + 1], $depthLimit, true);
        }
        $codeNumber = intval(substr($code, 1, -1));
        $ans += $shortest * $codeNumber;
    }

    return $ans;
}

echo solve($lines, 2) . "\n";
echo solve($lines, 25) . "\n";
