<?php

$DEBUG = false;
$filename = 'inputs/day20.txt';
$lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

function dfs(array &$grid, array &$path, int $r, int $c): void
{
    $n = count($grid);
    $m = strlen($grid[0]);
    $path[] = [$r, $c];

    if ($grid[$r][$c] === 'E') {
        return;
    }

    foreach ([[$r + 1, $c], [$r - 1, $c], [$r, $c + 1], [$r, $c - 1]] as list($nr, $nc)) {
        if (0 <= $nr and $nr < $n and 0 <= $nc and $nc < $m and !in_array([$nr, $nc], $path) and $grid[$nr][$nc] !== '#') {
            dfs($grid, $path, $nr, $nc);
        }
    }
}

/**
 * The problem relies on the fact that there's one clear path to the end.
 * 
 * In other words, cheats just try to "teleport" to a cell later in time (that the cheat distance allows).
 */
function solve(array &$grid, int $cheatLimit): int
{
    global $DEBUG;
    $n = count($grid);
    $m = strlen($grid[0]);

    for ($r = 0; $r < $n; $r++) {
        for ($c = 0; $c < $m; $c++) {
            if ($grid[$r][$c] === 'S') {
                $startRow = $r;
                $startCol = $c;
            }
        }
    }

    $path = [];
    dfs($grid, $path, $startRow, $startCol);

    $ans = 0;
    $check = [];

    for ($i = 0; $i < count($path) - 1; $i++) {
        for ($j = $i + 1; $j < count($path); $j++) {
            $dist = abs($path[$i][0] - $path[$j][0]) + abs($path[$i][1] - $path[$j][1]);

            if (2 <= $dist and $dist <= $cheatLimit) {
                $timeSaved = $j - $i - $dist;
                $ans += ($timeSaved >= 100);

                if ($DEBUG) {
                    if (!isset($check[$timeSaved])) {
                        $check[$timeSaved] = 0;
                    }
                    $check[$timeSaved]++;
                }
            }
        }
    }

    if ($DEBUG) {
        var_dump($check);
    }

    return $ans;
}

echo solve($lines, 2) . "\n";
echo solve($lines, 20) . "\n";
