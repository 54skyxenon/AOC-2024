<?php

use \Ds\Queue;

$filename = 'inputs/day15.txt';
$lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$grid = [];
$instructions = '';

foreach ($lines as $line) {
    if (str_contains($line, '<') or str_contains($line, '>') or str_contains($line, '^') or str_contains($line, 'v')) {
        $instructions = $instructions . $line;
    } else {
        $grid[] = $line;
    }
}

function dilate(array &$grid): array
{
    $dilated = [];

    foreach ($grid as $gridRow) {
        $dilated[] = implode('', array_map(fn($cell) => match (true) {
            $cell === '@' => '@.',
            $cell === '.' => '..',
            $cell === '#' => '##',
            $cell === 'O' => '[]',
        }, str_split($gridRow)));
    }

    return $dilated;
}

function simulate(array $grid, string &$instructions): int
{
    $row = null;
    $col = null;

    for ($r = 0; $r < count($grid); $r++) {
        for ($c = 0; $c < strlen($grid[0]); $c++) {
            if ($grid[$r][$c] === '@') {
                $row = $r;
                $col = $c;
            }
        }
    }

    foreach (str_split($instructions) as $move) {
        list($dr, $dc) = match (true) {
            $move === '<' => [0, -1],
            $move === '>' => [0, 1],
            $move === '^' => [-1, 0],
            $move === 'v' => [1, 0],
        };

        if ($grid[$row + $dr][$col + $dc] === '.') {
            $grid[$row + $dr][$col + $dc] = '@';
            $grid[$row][$col] = '.';
            $row += $dr;
            $col += $dc;
        } elseif ($grid[$row + $dr][$col + $dc] === '#') {
            // no-op if blocked
        } else {
            assert($grid[$row + $dr][$col + $dc] === 'O');
            $steps = 2;
            while ($grid[$row + $steps * $dr][$col + $steps * $dc] === 'O') {
                $steps++;
            }

            if ($grid[$row + $steps * $dr][$col + $steps * $dc] === '.') {
                $grid[$row + $steps * $dr][$col + $steps * $dc] = 'O';
                $grid[$row + $dr][$col + $dc] = '@';
                $grid[$row][$col] = '.';
                $row += $dr;
                $col += $dc;
            }
        }
    }

    $ans = 0;

    for ($r = 0; $r < count($grid); $r++) {
        for ($c = 0; $c < strlen($grid[0]); $c++) {
            if ($grid[$r][$c] === 'O') {
                $ans += 100 * $r + $c;
            }
        }
    }

    return $ans;
}

function simulateDilated(array $grid, string &$instructions): int
{
    $grid = dilate($grid);
    $row = null;
    $col = null;

    for ($r = 0; $r < count($grid); $r++) {
        for ($c = 0; $c < strlen($grid[0]); $c++) {
            if ($grid[$r][$c] === '@') {
                $row = $r;
                $col = $c;
            }
        }
    }

    foreach (str_split($instructions) as $move) {
        list($dr, $dc) = match (true) {
            $move === '<' => [0, -1],
            $move === '>' => [0, 1],
            $move === '^' => [-1, 0],
            $move === 'v' => [1, 0],
        };

        if ($grid[$row + $dr][$col + $dc] === '.') {
            $grid[$row + $dr][$col + $dc] = '@';
            $grid[$row][$col] = '.';
            $row += $dr;
            $col += $dc;
        } elseif ($grid[$row + $dr][$col + $dc] === '#') {
            // no-op if blocked
        } else {
            assert($grid[$row + $dr][$col + $dc] === '[' or $grid[$row + $dr][$col + $dc] === ']');

            // Horizontal moves are easier to deal with
            if (abs($dc) === 1) {
                $steps = 3;
                while ($grid[$row][$col + $steps * $dc] === '[' or $grid[$row][$col + $steps * $dc] === ']') {
                    $steps++;
                }

                // Nothing blocking a horizontal push
                if ($grid[$row][$col + $steps * $dc] === '.') {
                    $currCol = $col + $steps * $dc;

                    for ($step = 0; $step < $steps; $step++) {
                        $tmp = $grid[$row][$currCol];
                        $grid[$row][$currCol] = $grid[$row][$currCol - $dc];
                        $grid[$row][$currCol - $dc] = $tmp;
                        $currCol -= $dc;
                    }

                    $row += $dr;
                    $col += $dc;
                }
            } else {
                // Every bracket we need to shift vertically
                $visited = [[$row, $col, '@']];
                $bfs = new Queue([[$row, $col, '@']]);

                while (!$bfs->isEmpty()) {
                    list($currRow, $currCol) = $bfs->pop();

                    if ($grid[$currRow + $dr][$currCol] === '[') {
                        $state = [$currRow + $dr, $currCol, $grid[$currRow + $dr][$currCol]];
                        if (!in_array($state, $visited)) {
                            $visited[] = $state;
                            $bfs->push($state);
                        }

                        $state = [$currRow + $dr, $currCol + 1, $grid[$currRow + $dr][$currCol + 1]];
                        if (!in_array($state, $visited)) {
                            $visited[] = $state;
                            $bfs->push($state);
                        }
                    } elseif ($grid[$currRow + $dr][$currCol] === ']') {
                        $state = [$currRow + $dr, $currCol, $grid[$currRow + $dr][$currCol]];
                        if (!in_array($state, $visited)) {
                            $visited[] = $state;
                            $bfs->push($state);
                        }

                        $state = [$currRow + $dr, $currCol - 1, $grid[$currRow + $dr][$currCol - 1]];
                        if (!in_array($state, $visited)) {
                            $visited[] = $state;
                            $bfs->push($state);
                        }
                    }
                }

                $noBlockers = true;
                foreach ($visited as list($r, $c, $_)) {
                    if ($grid[$r + $dr][$c] === '#') {
                        $noBlockers = false;
                        break;
                    }
                }

                if ($noBlockers) {
                    foreach ($visited as list($r, $c, $_)) {
                        $grid[$r][$c] = '.';
                    }
                    foreach ($visited as list($r, $c, $symbol)) {
                        $grid[$r + $dr][$c] = $symbol;
                    }
                    $row += $dr;
                    $col += $dc;
                }
            }
        }
    }

    $ans = 0;

    for ($r = 0; $r < count($grid); $r++) {
        for ($c = 0; $c < strlen($grid[0]); $c++) {
            if ($grid[$r][$c] === '[') {
                $ans += 100 * $r + $c;
            }
        }
    }

    return $ans;
}

echo simulate($grid, $instructions) . "\n";
echo simulateDilated($grid, $instructions) . "\n";
