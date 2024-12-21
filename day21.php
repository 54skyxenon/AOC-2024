<?php

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

function transform(array $currentParameters, array $targetParameters): int
{
    global $ARROW_PAD_LOCATIONS, $NUMERIC_PAD_LOCATIONS;
    assert(count($currentParameters) === count($targetParameters), "Lengths of current and desired parameters don't match!\n");

    for ($i = 0; $i < count($currentParameters); $i++) {
        if ($currentParameters[$i] != $targetParameters[$i]) {
            // We are on the number keypad
            if ($i === 0) {
                $best = INF;
                $currentDistance = manhattanDistanceNums($currentParameters[$i], $targetParameters[$i]);
                list($r, $c) = $NUMERIC_PAD_LOCATIONS[$currentParameters[$i]];

                foreach ([[$r + 1, $c, 'v'], [$r - 1, $c, '^'], [$r, $c + 1, '>'], [$r, $c - 1, '<']] as list($nr, $nc, $newNextParameter)) {
                    $adjacentNum = array_search([$nr, $nc], $NUMERIC_PAD_LOCATIONS);

                    // The adjacent numeric key needs to exist and get us closer
                    if ($adjacentNum !== false) {
                        $newDistance = manhattanDistanceNums($adjacentNum, $targetParameters[$i]);

                        if ($newDistance < $currentDistance) {
                            $intermediaryTargetParameters = $currentParameters;
                            $intermediaryTargetParameters[$i + 1] = $newNextParameter;
                            for ($j = $i + 2; $j < count($intermediaryTargetParameters); $j++) {
                                $intermediaryTargetParameters[$j] = 'A';
                            }
                            $strokesToMoveNextParameter = transform($currentParameters, $intermediaryTargetParameters);
                            $newCurrentParameters = $intermediaryTargetParameters;
                            $newCurrentParameters[$i] = $adjacentNum;
                            $best = min($best, $strokesToMoveNextParameter + 1 + transform($newCurrentParameters, $targetParameters));
                        }
                    }
                }

                return $best;
            }
            // Robot on directional keypad
            elseif ($i < count($currentParameters) - 1) {
                $best = INF;
                $currentDistance = manhattanDistanceArrows($currentParameters[$i], $targetParameters[$i]);
                list($r, $c) = $ARROW_PAD_LOCATIONS[$currentParameters[$i]];

                foreach ([[$r + 1, $c, 'v'], [$r - 1, $c, '^'], [$r, $c + 1, '>'], [$r, $c - 1, '<']] as list($nr, $nc, $newNextParameter)) {
                    $adjacentArrow = array_search([$nr, $nc], $ARROW_PAD_LOCATIONS);

                    // The adjacent numeric key needs to exist and get us closer
                    if ($adjacentArrow !== false) {
                        $newDistance = manhattanDistanceArrows($adjacentArrow, $targetParameters[$i]);

                        if ($newDistance < $currentDistance) {
                            $intermediaryTargetParameters = $currentParameters;
                            $intermediaryTargetParameters[$i + 1] = $newNextParameter;
                            for ($j = $i + 2; $j < count($intermediaryTargetParameters); $j++) {
                                $intermediaryTargetParameters[$j] = 'A';
                            }
                            $strokesToMoveNextParameter = transform($currentParameters, $intermediaryTargetParameters);
                            $newCurrentParameters = $intermediaryTargetParameters;
                            $newCurrentParameters[$i] = $adjacentArrow;
                            $best = min($best, $strokesToMoveNextParameter + 1 + transform($newCurrentParameters, $targetParameters));
                        }
                    }
                }

                return $best;
            }
            // We can directly control the last robot 
            else {
                $myStrokes = manhattanDistanceArrows($currentParameters[$i], $targetParameters[$i]);
                $newParameters = $currentParameters;
                $newParameters[count($newParameters) - 1] = end($targetParameters);
                $restOfJourney = transform($newParameters, $targetParameters);
                return $myStrokes + $restOfJourney;
            }
        }
    }

    // All parameters match
    return 0;
}

function shortestSequence(string $code, array $parameters): int
{
    $keyPresses = 0;

    // Line up all the robot arms for the press, each time
    for ($typed = 0; $typed < strlen($code); $typed++) {
        $targetParameters = [$code[$typed], ...array_fill(0, count($parameters) - 1, 'A')];
        $cost = transform($parameters, $targetParameters);
        $keyPresses += $cost + 1;
        $parameters = $targetParameters;
    }

    return $keyPresses;
}

function solve(array &$lines, int $robotDirectionalKeypads)
{
    $ans = 0;

    foreach ($lines as $code) {
        $initialParameters = array_fill(0, $robotDirectionalKeypads + 1, 'A');
        $codeNumber = intval(substr($code, 0, -1));
        $ans += shortestSequence($code, $initialParameters) * $codeNumber;
    }

    return $ans;
}

// Works
echo solve($lines, 2) . "\n";

// ! Takes forever to do, THINK OF SOMETHING BETTER
echo solve($lines, 25) . "\n";
