<?php
$filename = "inputs/day1.txt";
$lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$left = [];
$right = [];

foreach ($lines as $line) {
    list($left[], $right[]) = array_map('intval', preg_split('/\s+/', $line));
}

sort($left);
sort($right);

function solvePart1(): int
{
    global $left, $right;

    $sum = 0;

    foreach (array_map(null, $left, $right) as $pair) {
        $sum += abs($pair[0] - $pair[1]);
    }

    return $sum;
}

function solvePart2(): int
{
    global $left, $right;

    $sum = 0;
    $counter = array_count_values($right);

    foreach ($left as $multiplicand) {
        $sum += $multiplicand * ($counter[$multiplicand] ?? 0);
    }

    return $sum;
}

echo solvePart1() . "\n";
echo solvePart2() . "\n";
