<?php
$filename = 'inputs/day14.txt';
$lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

function extractNumbers(string $line): array
{
    preg_match_all('/-?\d+/', $line, $matches);
    return array_map('intval', $matches[0]);
}

function getFinalPositions(array &$lines, int $xLimit, int $yLimit, int $elapsedSeconds): array
{
    $finalPositions = [];

    foreach ($lines as $line) {
        list($px, $py, $vx, $vy) = extractNumbers($line);

        // GMP is needed to guarantee a non-negative mod value
        $finalPx = gmp_intval(gmp_mod($px + $elapsedSeconds * $vx, $xLimit));
        $finalPy = gmp_intval(gmp_mod($py + $elapsedSeconds * $vy, $yLimit));
        $finalPositions[] = [$finalPx, $finalPy];
    }

    return $finalPositions;
}

function solvePart1(array &$lines, int $xLimit, int $yLimit): int
{
    $xMiddle = intdiv($xLimit, 2);
    $yMiddle = intdiv($yLimit, 2);
    $quadrants = array_fill(0, 4, 0);

    foreach (getFinalPositions($lines, $xLimit, $yLimit, 100) as list($finalPx, $finalPy)) {
        if ($finalPx !== $xMiddle and $finalPy !== $yMiddle) {
            if ($finalPx < $xMiddle and $finalPy < $yMiddle) {
                $quadrants[0]++;
            } elseif ($finalPx < $xMiddle and $finalPy > $yMiddle) {
                $quadrants[1]++;
            } elseif ($finalPx > $xMiddle and $finalPy < $yMiddle) {
                $quadrants[2]++;
            } else {
                $quadrants[3]++;
            }
        }
    }

    return array_product($quadrants);
}

// I did Ctrl-F "1 1 1 1 1 1 1 1 1 1 1"... in terminal output
function solvePart2(array &$lines, int $xLimit, int $yLimit): void
{
    for ($elapsedSeconds = 0; $elapsedSeconds < 10000; $elapsedSeconds++) {
        $grid = array_fill(0, $yLimit, array_fill(0, $xLimit, '.'));
        foreach (getFinalPositions($lines, $xLimit, $yLimit, $elapsedSeconds) as list($finalPx, $finalPy)) {
            $grid[$finalPy][$finalPx] = '1';
        }

        echo "\n\n=== AFTER $elapsedSeconds SECONDS ===\n";
        foreach ($grid as $row) {
            echo implode(' ', $row) . "\n";
        }
    }
}

echo solvePart1($lines, 101, 103) . "\n";
solvePart2($lines, 101, 103);
