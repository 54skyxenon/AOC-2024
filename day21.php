<?php

use \Ds\Map;

$filename = 'inputs/day21.txt';
$lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$NUMERIC_PAD_LOCATIONS = [
    '7' => [0, 0],
    '8' => [0, 1],
    '9' => [0, 2],
    '4' => [1, 0],
    '5' => [1, 1],
    '6' => [1, 2],
    '1' => [2, 0],
    '2' => [2, 1],
    '3' => [2, 2],
    '0' => [3, 1],
    'A' => [3, 2],
];

$ARROW_PAD_LOCATIONS = [
    '^' => [0, 1],
    'A' => [0, 2],
    '<' => [1, 0],
    'v' => [1, 1],
    '>' => [1, 2],
];

$transformCache = new Map();

function manhattanDistanceArrows(string $arrow1, string $arrow2): int
{
    global $ARROW_PAD_LOCATIONS;
    return abs($ARROW_PAD_LOCATIONS[$arrow1][0] - $ARROW_PAD_LOCATIONS[$arrow2][0]) + abs($ARROW_PAD_LOCATIONS[$arrow1][1] - $ARROW_PAD_LOCATIONS[$arrow2][1]);
}

function manhattanDistanceNums(string $num1, string $num2): int
{
    global $NUMERIC_PAD_LOCATIONS;
    return abs($NUMERIC_PAD_LOCATIONS[$num1][0] - $NUMERIC_PAD_LOCATIONS[$num2][0]) + abs($NUMERIC_PAD_LOCATIONS[$num1][1] - $NUMERIC_PAD_LOCATIONS[$num2][1]);
}

function transform(string $currentNum, string $currentArrow1, string $currentArrow2, string $targetNum, string $targetArrow1, string $targetArrow2): int
{
    global $ARROW_PAD_LOCATIONS, $NUMERIC_PAD_LOCATIONS, $transformCache;

    $state = "$currentNum,$currentArrow1,$currentArrow2 => $targetNum,$targetArrow1,$targetArrow2";

    if ($transformCache->hasKey($state)) {
        return $transformCache[$state];
    }

    // Do what it takes to make the number match
    if ($currentNum !== $targetNum) {
        $best = INF;

        $currentDistance = manhattanDistanceNums($currentNum, $targetNum);
        list($r, $c) = $NUMERIC_PAD_LOCATIONS[$currentNum];

        foreach ([[$r + 1, $c, 'v'], [$r - 1, $c, '^'], [$r, $c + 1, '>'], [$r, $c - 1, '<']] as list($nr, $nc, $newTargetArrow1)) {
            $adjacentNum = array_search([$nr, $nc], $NUMERIC_PAD_LOCATIONS);

            // The adjacent numeric key needs to exist and get us closer
            if ($adjacentNum !== false) {
                $newDistance = manhattanDistanceNums($adjacentNum, $targetNum);

                if ($newDistance < $currentDistance) {
                    $strokesToMoveArrow1 = transform($currentNum, $currentArrow1, $currentArrow2, $currentNum, $newTargetArrow1, 'A');
                    $best = min($best, $strokesToMoveArrow1 + 1 + transform($adjacentNum, $newTargetArrow1, 'A', $targetNum, $targetArrow1, $targetArrow2));
                }
            }
        }

        return $transformCache[$state] = $best;
    }
    // One layer of indirection
    elseif ($currentArrow1 !== $targetArrow1) {
        $best = INF;

        $currentDistance = manhattanDistanceArrows($currentArrow1, $targetArrow1);
        list($r, $c) = $ARROW_PAD_LOCATIONS[$currentArrow1];

        foreach ([[$r + 1, $c, 'v'], [$r - 1, $c, '^'], [$r, $c + 1, '>'], [$r, $c - 1, '<']] as list($nr, $nc, $newTargetArrow2)) {
            $adjacentArrow = array_search([$nr, $nc], $ARROW_PAD_LOCATIONS);

            // The adjacent arrow key needs to exist and get us closer
            if ($adjacentArrow) {
                $newDistance = manhattanDistanceArrows($adjacentArrow, $targetArrow1);

                if ($newDistance < $currentDistance) {
                    $strokesToMoveArrow2 = transform($currentNum, $currentArrow1, $currentArrow2, $currentNum, $currentArrow1, $newTargetArrow2);
                    // ? Is +1 needed for the pressing
                    $best = min($best, $strokesToMoveArrow2 + 1 + transform($currentNum, $adjacentArrow, $newTargetArrow2, $targetNum, $targetArrow1, $targetArrow2));
                }
            }
        }

        return $transformCache[$state] = $best;
    }
    // The layer we can directly control
    elseif ($currentArrow2 !== $targetArrow2) {
        $myStrokes = manhattanDistanceArrows($currentArrow2, $targetArrow2);
        $restOfJourney = transform($currentNum, $currentArrow1, $targetArrow2, $targetNum, $targetArrow1, $targetArrow2);
        return $transformCache[$state] = ($myStrokes + $restOfJourney);
    } else {
        return $transformCache[$state] = 0;
    }
}

function dfs(string $code, string $currentNum, string $currentArrow1, string $currentArrow2, int $typed, int $strokes): int
{
    if ($typed === strlen($code)) {
        return $strokes;
    }

    // Line up all the robot arms for the press
    $cost = transform($currentNum, $currentArrow1, $currentArrow2, $code[$typed], 'A', 'A');
    return dfs($code, $code[$typed], 'A', 'A', $typed + 1, $strokes + $cost + 1);
}

function codeNumber(string $code): int
{
    return intval(substr($code, 0, -1));
}

$part1 = array_sum(array_map(fn($code) => dfs($code, 'A', 'A', 'A', 0, 0) * codeNumber($code), $lines));
echo "$part1\n";
