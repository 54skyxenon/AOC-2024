<?php

use \Ds\Map;
use \Ds\Set;
use \Ds\Queue;

$filename = 'inputs/day12.txt';
$lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

enum Direction: string
{
    case NORTH = 'N';
    case SOUTH = 'S';
    case WEST = 'W';
    case EAST = 'E';
};

class ComponentData
{
    public array $outerEdges;
    public Set $cells;

    public function __construct(array $outerEdges, Set $cells)
    {
        $this->outerEdges = $outerEdges;
        $this->cells = $cells;
    }

    public function perimeter(): int
    {
        return count($this->outerEdges);
    }

    public function area(): int
    {
        return $this->cells->count();
    }
}

function getDataForComponents(array &$lines): array
{
    $n = count($lines);
    $m = strlen($lines[0]);
    $visited = new Set();
    $components = [];

    for ($row = 0; $row < $n; $row++) {
        for ($col = 0; $col < $m; $col++) {
            $startingCell = "$row,$col";
            if ($visited->contains($startingCell)) {
                continue;
            }

            $perimeter = 0;
            $outerEdges = [];
            $visitedHere = new Set([$startingCell]);
            $bfs = new Queue([[$row, $col]]);
            while (!$bfs->isEmpty()) {
                list($r, $c) = $bfs->pop();
                foreach ([[$r - 1, $c, Direction::NORTH], [$r + 1, $c, Direction::SOUTH], [$r, $c - 1, Direction::WEST], [$r, $c + 1, Direction::EAST]] as list($nr, $nc, $d)) {
                    if (0 <= $nr and $nr < $n and 0 <= $nc and $nc < $m and $lines[$nr][$nc] === $lines[$r][$c]) {
                        if (!$visitedHere->contains("$nr,$nc")) {
                            $visitedHere->add("$nr,$nc");
                            $bfs->push([$nr, $nc]);
                        }
                    } else {
                        if ($r !== $nr) {
                            $outerEdges[] = [$d, ($r + $nr) / 2, ($c + ($c + 1)) / 2];
                        } else { // $c !== $nc
                            $outerEdges[] = [$d, ($c + $nc) / 2, ($r + ($r + 1)) / 2];
                        }
                        $perimeter++;
                    }
                }
            }

            $visited->add(...$visitedHere->toArray());
            $components[] = new ComponentData($outerEdges, $visitedHere);
        }
    }

    return $components;
}

function solvePart1(array &$lines): int
{
    $ans = 0;

    foreach (getDataForComponents($lines) as $componentData) {
        $ans += $componentData->perimeter() * $componentData->area();
    }

    return $ans;
}

function solvePart2(array &$lines): int
{
    $ans = 0;

    foreach (getDataForComponents($lines) as $componentData) {
        $axisData = new Map();

        foreach ($componentData->outerEdges as $edge) {
            $axisKey = $edge[0]->value . '|' . strval($edge[1]);
            if (!$axisData->hasKey($axisKey)) {
                $axisData[$axisKey] = new Set();
            }
            $axisData[$axisKey]->add($edge[2]);
        }

        $sides = 0;

        foreach ($axisData->values() as $pointsSequence) {
            $points = $pointsSequence->toArray();
            sort($points);

            for ($i = 0; $i < count($points); $i++) {
                if ($i === 0 or $points[$i] - 1 !== $points[$i - 1]) {
                    $sides++;
                }
            }
        }

        $ans += $sides * $componentData->area();
    }

    return $ans;
}

echo solvePart1($lines) . "\n";
echo solvePart2($lines) . "\n";
