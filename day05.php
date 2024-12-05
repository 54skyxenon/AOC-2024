<?php
$filename = 'inputs/day5.txt';
$lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$graph = [];
$ans = 0;
$ansPart2 = 0;

function isCorrectlyOrdered(array &$sequence)
{
    global $graph;

    $last = $sequence[0];
    for ($i = 1; $i < count($sequence); $i++) {
        if (!in_array($sequence[$i], ($graph[$last] ?? []))) {
            return false;
        }
        $last = $sequence[$i];
    }
    return true;
}

function dfs(int $curr, array $path, array &$subgraph, int|null &$newMiddle, array &$sequence): bool
{
    if (count($path) === count($sequence)) {
        $newMiddle = $path[intdiv(count($path), 2)];
        return true;
    }

    foreach (($subgraph[$curr] ?? []) as $nei) {
        if (!in_array($nei, $path) and dfs($nei, array_merge($path, [$nei]), $subgraph, $newMiddle, $sequence)) {
            return true;
        }
    }

    return false;
}

function fixIncorrectlyOrdered(array $sequence): int
{
    global $graph;

    $subgraph = [];
    for ($i = 0; $i < count($sequence) - 1; $i++) {
        for ($j = $i + 1; $j < count($sequence); $j++) {
            if (in_array($sequence[$j], $graph[$sequence[$i]] ?? [])) {
                $subgraph[$sequence[$i]] = [...($subgraph[$sequence[$i]] ?? []), $sequence[$j]];
            } elseif (in_array($sequence[$i], $graph[$sequence[$j]] ?? [])) {
                $subgraph[$sequence[$j]] = [...($subgraph[$sequence[$j]] ?? []), $sequence[$i]];
            }
        }
    }

    $newMiddle = null;

    foreach ($sequence as $start) {
        if (isset($newMiddle)) {
            break;
        }
        dfs($start, [$start], $subgraph, $newMiddle, $sequence);
    }

    assert(isset($newMiddle));
    return $newMiddle;
}

foreach ($lines as $line) {
    if (str_contains($line, '|')) {
        list($src, $dest) = array_map('intval', explode('|', $line));
        $graph[$src] = [...($graph[$src] ?? []), $dest];
    } else {
        $sequence = array_map('intval', explode(',', $line));

        if (isCorrectlyOrdered($sequence)) {
            assert(count($sequence) % 2 === 1);
            $ans += $sequence[intdiv(count($sequence), 2)];
        } else {
            $ansPart2 += fixIncorrectlyOrdered($sequence);
        }
    }
}

echo $ans . "\n";
echo $ansPart2 . "\n";
