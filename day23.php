<?php

use \Ds\Set;

$filename = 'inputs/day23.txt';
$lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

function buildGraph(array &$lines): array
{
    $graph = [];

    foreach ($lines as $line) {
        list($first, $second) = explode('-', $line);

        if (!array_key_exists($first, $graph)) {
            $graph[$first] = new Set();
        }
        $graph[$first]->add($second);

        if (!array_key_exists($second, $graph)) {
            $graph[$second] = new Set();
        }
        $graph[$second]->add($first);
    }

    return $graph;
}

$graph = buildGraph($lines);

function isClique(array $combination): bool
{
    global $graph;

    for ($i = 0; $i < count($combination) - 1; $i++) {
        for ($j = $i + 1; $j < count($combination); $j++) {
            if (!$graph[$combination[$i]]->contains($combination[$j])) {
                return false;
            }
        }
    }

    return true;
}

function anyCombinationIsClique(array &$candidates, array $path, int $index, int $remaining): bool
{
    global $graph;

    if ($remaining === 0) {
        $result = isClique($path, $graph);

        if ($result) {
            sort($path);
            echo "Clique found: " . implode(',', $path) . "\n";
        }

        return $result;
    }

    for ($i = $index; $i < count($candidates); $i++) {
        $prune = false;

        foreach ($path as $previous) {
            if (!$graph[$candidates[$i]]->contains($previous)) {
                $prune = true;
                break;
            }
        }

        if (!$prune and anyCombinationIsClique($candidates, [...$path, $candidates[$i]], $i + 1, $remaining - 1)) {
            return true;
        }
    }

    return false;
}

function solvePart1(): void
{
    global $graph;
    $nodes = array_keys($graph);

    $validTriplets = 0;

    for ($i = 0; $i < count($nodes) - 2; $i++) {
        for ($j = $i + 1; $j < count($nodes) - 1; $j++) {
            for ($k = $j + 1; $k < count($nodes); $k++) {
                $first = $nodes[$i];
                $second = $nodes[$j];
                $third = $nodes[$k];

                if (isClique([$first, $second, $third]) and (str_starts_with($first, 't') or str_starts_with($second, 't') or str_starts_with($third, 't'))) {
                    $validTriplets++;
                }
            }
        }
    }

    echo $validTriplets . "\n";
}

function solvePart2(): void
{
    global $graph;
    $nodes = array_keys($graph);

    // The upper bound for the binary search is the maximum degree of any given node
    $low = 0;
    $high = max(array_map(fn($node) => $graph[$node]->count(), $nodes));

    while ($low < $high) {
        $mid = intdiv($low + $high + 1, 2);
        if (anyCombinationIsClique($nodes, [], 0, $mid)) {
            $low = $mid;
        } else {
            $high = $mid - 1;
        }
    }
}

solvePart1();
solvePart2();
