<?php
$filename = "inputs/day2.txt";
$lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

function isSafeHelper(array $tokens)
{
    $differences = [];
    for ($i = 0; $i < count($tokens) - 1; $i++) {
        $differences[] = $tokens[$i + 1] - $tokens[$i];
    }

    if ($differences === array_filter($differences, function ($x) {
        return 1 <= $x and $x <= 3;
    })) {
        return true;
    }

    if ($differences === array_filter($differences, function ($x) {
        return -3 <= $x and $x <= -1;
    })) {
        return true;
    }

    return false;
}

function isSafe(string $line): bool
{
    $tokens = array_map('intval', explode(" ", $line));
    return isSafeHelper($tokens);
}

function isSafeWithRemoval(string $line): bool
{
    $tokens = array_map('intval', explode(" ", $line));

    if (isSafeHelper($tokens)) {
        return true;
    }

    for ($i = 0; $i < count($tokens); $i++) {
        if (isSafeHelper(array_merge(array_slice($tokens, 0, $i), array_slice($tokens, $i + 1)))) {
            return true;
        }
    }

    return false;
}

$numSafe = array_sum(array_map('isSafe', $lines));
echo $numSafe . "\n";

$numSafeWithRemoval = array_sum(array_map('isSafeWithRemoval', $lines));
echo $numSafeWithRemoval . "\n";
