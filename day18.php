<?php

use \Ds\Queue;

$filename = 'inputs/day18.txt';
$lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$MAX_SIZE = 71;

function minimumSteps(array &$grid, int $targetX, int $targetY): int|float
{
    global $MAX_SIZE;

    $visited = array_fill(0, $MAX_SIZE, array_fill(0, $MAX_SIZE, false));
    $visited[0][0] = true;
    $bfs = new Queue([[0, 0, 0]]);

    while (!$bfs->isEmpty()) {
        list($r, $c, $dist) = $bfs->pop();

        if ($r === $targetY and $c === $targetX) {
            return $dist;
        }

        foreach ([[$r - 1, $c], [$r + 1, $c], [$r, $c + 1], [$r, $c - 1]] as list($nr, $nc)) {
            if (0 <= $nr and $nr < $MAX_SIZE and 0 <= $nc and $nc < $MAX_SIZE and !$visited[$nr][$nc] and $grid[$nr][$nc] !== '#') {
                $visited[$nr][$nc] = true;
                $bfs->push([$nr, $nc, $dist + 1]);
            }
        }
    }

    return INF;
}

$grid = array_fill(0, $MAX_SIZE, array_fill(0, $MAX_SIZE, '.'));

foreach (array_slice($lines, 0, 1024) as $line) {
    list($c, $r) = explode(',', $line);
    $grid[$r][$c] = '#';
}

echo minimumSteps($grid, 70, 70) . "\n";

// Wait a few seconds for Part 2
foreach (array_slice($lines, 1024) as $line) {
    list($c, $r) = explode(',', $line);
    $grid[$r][$c] = '#';

    if (minimumSteps($grid, 70, 70) === INF) {
        echo "$c,$r\n";
        break;
    }
}
