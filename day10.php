<?php

use \Ds\Set;

$filename = 'inputs/day10.txt';
$lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

function dfs(array &$lines, object &$visited, int $row, int $col): void
{
    $n = count($lines);
    $m = strlen($lines[0]);
    $curr = intval($lines[$row][$col]);

    if ($curr === 9) {
        $visited->add("$row,$col");
        return;
    }

    foreach ([[0, -1], [1, 0], [-1, 0], [0, 1]] as list($dr, $dc)) {
        $nr = $row + $dr;
        $nc = $col + $dc;
        if (0 <= $nr and $nr < $n and 0 <= $nc and $nc < $m and intval($lines[$nr][$nc]) === $curr + 1) {
            dfs($lines, $visited, $nr, $nc);
        }
    }
}

function solvePart1(array &$lines): int
{
    $n = count($lines);
    $m = strlen($lines[0]);
    $ans = 0;

    for ($i = 0; $i < $n; $i++) {
        for ($j = 0; $j < $m; $j++) {
            if ($lines[$i][$j] === '0') {
                $visited = new Set();
                dfs($lines, $visited, $i, $j);
                $ans += $visited->count();
            }
        }
    }

    return $ans;
}

// cache[$row][$col] = valid paths continuing onwards from that cell 
function dp(array &$lines, array &$cache, int $row, int $col): int
{
    if ($cache[$row][$col] !== -1) {
        return $cache[$row][$col];
    }

    $n = count($lines);
    $m = strlen($lines[0]);
    $curr = intval($lines[$row][$col]);

    if ($curr === 9) {
        return $cache[$row][$col] = 1;
    }

    $ans = 0;

    foreach ([[0, -1], [1, 0], [-1, 0], [0, 1]] as list($dr, $dc)) {
        $nr = $row + $dr;
        $nc = $col + $dc;
        if (0 <= $nr and $nr < $n and 0 <= $nc and $nc < $m and intval($lines[$nr][$nc]) === $curr + 1) {
            $ans += dp($lines, $cache, $nr, $nc);
        }
    }

    return $cache[$row][$col] = $ans;
}

function solvePart2(array &$lines): int
{
    $n = count($lines);
    $m = strlen($lines[0]);
    $ans = 0;

    $cache = array_fill(0, $n, array_fill(0, $m, -1));

    for ($i = 0; $i < $n; $i++) {
        for ($j = 0; $j < $m; $j++) {
            if ($lines[$i][$j] === '0') {
                $ans += dp($lines, $cache, $i, $j);
            }
        }
    }

    return $ans;
}

echo solvePart1($lines) . "\n";
echo solvePart2($lines) . "\n";
