<?php

use \Ds\Set;
use \Ds\Queue;
use \Ds\PriorityQueue;

$filename = 'inputs/day16.txt';
$lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

function findSymbol(array &$lines, string $symbol): array
{
    $n = count($lines);
    $m = strlen($lines[0]);

    for ($r = 0; $r < $n; $r++) {
        for ($c = 0; $c < $m; $c++) {
            if ($lines[$r][$c] === $symbol) {
                return [$r, $c];
            }
        }
    }

    throw new InvalidArgumentException("Symbol not found in input grid!");
}

function solve(array &$lines): array
{
    $n = count($lines);
    $m = strlen($lines[0]);

    list($startRow, $startCol) = findSymbol($lines, 'S');

    // CW: East, South, West, North 
    $movements = [[0, 1], [1, 0], [0, -1], [-1, 0]];

    // Part 1 answer states
    $dist = array_fill(0, $n, array_fill(0, $m, [INF, INF, INF, INF]));
    $dist[$startRow][$startCol][0] = 0;
    $dijkstra = new PriorityQueue();
    $dijkstra->push([0, $startRow, $startCol, 0], 0);

    // Part 2 answer state
    $incoming = array_fill(0, $n, array_fill(0, $m, [[], [], [], []]));

    while (!$dijkstra->isEmpty()) {
        list($d, $r, $c, $direction) = $dijkstra->pop();

        if ($dist[$r][$c][$direction] < $d) {
            continue;
        }

        // Proceed
        list($dr, $dc) = $movements[$direction];
        if ($lines[$r + $dr][$c + $dc] !== '#') {
            if ($d + 1 <= $dist[$r + $dr][$c + $dc][$direction]) {
                if ($d + 1 < $dist[$r + $dr][$c + $dc][$direction]) {
                    $dist[$r + $dr][$c + $dc][$direction] = $d + 1;
                    $dijkstra->push([$d + 1, $r + $dr, $c + $dc, $direction], - ($d + 1));
                    $incoming[$r + $dr][$c + $dc][$direction] = [];
                }
                $incoming[$r + $dr][$c + $dc][$direction][] = [$r, $c, $direction];
            }
        }

        // Turn CW
        if ($d + 1000 <= $dist[$r][$c][($direction + 3) % 4]) {
            if ($d + 1000 < $dist[$r][$c][($direction + 3) % 4]) {
                $dist[$r][$c][($direction + 3) % 4] = $d + 1000;
                $dijkstra->push([$d + 1000, $r, $c, ($direction + 3) % 4], - ($d + 1000));
                $incoming[$r][$c][($direction + 3) % 4] = [];
            }
            $incoming[$r][$c][($direction + 3) % 4][] = [$r, $c, $direction];
        }

        // Turn CCW
        if ($d + 1000 <= $dist[$r][$c][($direction + 1) % 4]) {
            if ($d + 1000 < $dist[$r][$c][($direction + 1) % 4]) {
                $dist[$r][$c][($direction + 1) % 4] = $d + 1000;
                $dijkstra->push([$d + 1000, $r, $c, ($direction + 1) % 4], - ($d + 1000));
                $incoming[$r][$c][($direction + 1) % 4] = [];
            }
            $incoming[$r][$c][($direction + 1) % 4][] = [$r, $c, $direction];
        }
    }

    list($endRow, $endCol) = findSymbol($lines, 'E');

    $endDirection = 0;
    $closestDist = $dist[$endRow][$endCol][0];
    for ($otherDirection = 1; $otherDirection < 4; $otherDirection++) {
        if ($closestDist > $dist[$endRow][$endCol][$otherDirection]) {
            $endDirection = $otherDirection;
            $closestDist = $dist[$endRow][$endCol][$otherDirection];
        }
    }

    $visited = new Set(["$endRow,$endCol,$endDirection"]);
    $uniqueTiles = new Set(["$endRow, $endCol"]);
    $bfs = new Queue([[$endRow, $endCol, $endDirection]]);

    while (!$bfs->isEmpty()) {
        list($r, $c, $direction) = $bfs->pop();

        foreach ($incoming[$r][$c][$direction] as list($nr, $nc, $nDirection)) {
            $state = "$nr,$nc,$nDirection";
            if (!$visited->contains($state)) {
                $visited->add($state);
                $uniqueTiles->add("$nr,$nc");
                $bfs->push([$nr, $nc, $nDirection]);
            }
        }
    }

    return [$closestDist, $uniqueTiles->count()];
}

list($part1, $part2) = solve($lines);
echo "$part1\n$part2\n";
