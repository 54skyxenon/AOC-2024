<?php

use \Ds\Set;

$filename = 'inputs/day8.txt';
$lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

function getAntennaLocations(array &$lines): array
{
    $antennaLocations = [];

    for ($row = 0; $row < count($lines); $row++) {
        for ($col = 0; $col < strlen($lines[$row]); $col++) {
            $frequency = $lines[$row][$col];
            if ($frequency !== '.') {
                $antennaLocations[$frequency] = [...($antennaLocations[$frequency] ?? []), [$row, $col]];
            }
        }
    }

    return $antennaLocations;
}

function solvePart1(array &$lines): int
{
    $antennaLocations = getAntennaLocations($lines);
    $spots = new Set();

    foreach (array_values($antennaLocations) as $locations) {
        for ($i = 0; $i < count($locations) - 1; $i++) {
            for ($j = $i + 1; $j < count($locations); $j++) {
                $dx = $locations[$j][0] - $locations[$i][0];
                $dy = $locations[$j][1] - $locations[$i][1];

                $beforeX = $locations[$i][0] - $dx;
                $beforeY = $locations[$i][1] - $dy;
                if (0 <= $beforeX and $beforeX < strlen($lines[0]) and 0 <= $beforeY and $beforeY < count($lines)) {
                    $spots->add("$beforeX,$beforeY");
                }

                $afterX = $locations[$j][0] + $dx;
                $afterY = $locations[$j][1] + $dy;
                if (0 <= $afterX and $afterX < strlen($lines[0]) and 0 <= $afterY and $afterY < count($lines)) {
                    $spots->add("$afterX,$afterY");
                }
            }
        }
    }

    return $spots->count();
}

function solvePart2(array &$lines): int
{
    $antennaLocations = getAntennaLocations($lines);
    $spots = new Set();

    foreach (array_values($antennaLocations) as $locations) {
        for ($i = 0; $i < count($locations) - 1; $i++) {
            for ($j = $i + 1; $j < count($locations); $j++) {
                $dx = $locations[$j][0] - $locations[$i][0];
                $dy = $locations[$j][1] - $locations[$i][1];

                list($x, $y) = $locations[$i];
                while (0 <= $x and $x < strlen($lines[0]) and 0 <= $y and $y < count($lines)) {
                    $spots->add("$x,$y");
                    $x -= $dx;
                    $y -= $dy;
                }

                list($x, $y) = $locations[$i];
                while (0 <= $x and $x < strlen($lines[0]) and 0 <= $y and $y < count($lines)) {
                    $spots->add("$x,$y");
                    $x += $dx;
                    $y += $dy;
                }
            }
        }
    }

    return $spots->count();
}

echo solvePart1($lines) . "\n";
echo solvePart2($lines) . "\n";
