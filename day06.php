<?php

use \Ds\Set;

$filename = 'inputs/day6.txt';
$lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

function findStartLocation(array &$lines): array
{
    $n = count($lines);
    $m = strlen($lines[0]);

    for ($row = 0; $row < $n; $row++) {
        for ($col = 0; $col < $m; $col++) {
            if ($lines[$row][$col] === '^') {
                return [$row, $col];
            }
        }
    }

    throw new InvalidArgumentException("No starting coordinate found!");
}

function simulate(array $lines, int $startRow, int $startCol): int
{
    $n = count($lines);
    $m = strlen($lines[0]);

    $row = $startRow;
    $col = $startCol;
    $visitedCoordinates = new Set(["$row,$col"]);
    $visitedStates = new Set(["$row,$col,^"]);

    while (true) {
        if ($lines[$row][$col] === '^') {
            if ($row === 0) {
                break;
            } else if ($lines[$row - 1][$col] === '.') {
                $lines[$row - 1][$col] = '^';
                $lines[$row][$col] = '.';
                $row--;
            } else {
                $lines[$row][$col] = '>';
            }
        } else if ($lines[$row][$col] === '>') {
            if ($col === $m - 1) {
                break;
            } else if ($lines[$row][$col + 1] === '.') {
                $lines[$row][$col + 1] = '>';
                $lines[$row][$col] = '.';
                $col++;
            } else {
                $lines[$row][$col] = 'v';
            }
        } else if ($lines[$row][$col] === '<') {
            if ($col === 0) {
                break;
            } else if ($lines[$row][$col - 1] === '.') {
                $lines[$row][$col - 1] = '<';
                $lines[$row][$col] = '.';
                $col--;
            } else {
                $lines[$row][$col] = '^';
            }
        } else if ($lines[$row][$col] === 'v') {
            if ($row === $n - 1) {
                break;
            } else if ($lines[$row + 1][$col] === '.') {
                $lines[$row + 1][$col] = 'v';
                $lines[$row][$col] = '.';
                $row++;
            } else {
                $lines[$row][$col] = '<';
            }
        } else {
            throw new UnexpectedValueException("Currently seeing " . $lines[$row][$col] . "\n");
        }

        $direction = $lines[$row][$col];
        if ($visitedStates->contains("$row,$col,$direction")) {
            throw new InvalidArgumentException("Stuck in infinite loop!");
        }

        $visitedCoordinates->add("$row,$col");
        $visitedStates->add("$row,$col,$direction");
    }

    return $visitedCoordinates->count();
}

function countBlockades(array &$lines): int
{
    $n = count($lines);
    $m = strlen($lines[0]);
    $blockades = 0;

    for ($row = 0; $row < $n; $row++) {
        for ($col = 0; $col < $m; $col++) {
            if ($lines[$row][$col] !== '.') {
                continue;
            }

            try {
                $lines[$row][$col] = '#';
                simulate($lines, ...findStartLocation($lines));
            } catch (InvalidArgumentException $e) {
                $blockades++;
            }

            $lines[$row][$col] = '.';
        }
    }

    return $blockades;
}

echo simulate($lines, ...findStartLocation($lines)) . "\n";
echo countBlockades($lines) . "\n";
