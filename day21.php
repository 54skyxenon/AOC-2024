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
    assert(count($currentParameters) === count($targetParameters));

    for ($i = 0; $i < count($currentParameters); $i++) {
        if ($currentParameters[$i] != $targetParameters[$i]) {
            // Robots indirectly controlled by other robots
            if ($i < count($currentParameters) - 1) {
                $best = INF;
                $locations = ($i > 0) ? $ARROW_PAD_LOCATIONS : $NUMERIC_PAD_LOCATIONS;
                $distance = ($i > 0) ? (fn($x, $y) => manhattanDistanceArrows($x, $y)) : (fn($x, $y) => manhattanDistanceNums($x, $y));

                $currentDistance = $distance($currentParameters[$i], $targetParameters[$i]);
                list($r, $c) = $locations[$currentParameters[$i]];

                foreach ([[$r + 1, $c, 'v'], [$r - 1, $c, '^'], [$r, $c + 1, '>'], [$r, $c - 1, '<']] as list($nr, $nc, $newNextParameter)) {
                    $adjacent = array_search([$nr, $nc], $locations);

                    // The adjacent key needs to exist and get us closer
                    if ($adjacent !== false and $distance($adjacent, $targetParameters[$i]) < $currentDistance) {
                        $intermediaryTargetParameters = $currentParameters;
                        $intermediaryTargetParameters[$i + 1] = $newNextParameter;
                        for ($j = $i + 2; $j < count($intermediaryTargetParameters); $j++) {
                            $intermediaryTargetParameters[$j] = 'A';
                        }
                        $strokesToMoveNextParameter = transform($currentParameters, $intermediaryTargetParameters);
                        $newCurrentParameters = $intermediaryTargetParameters;
                        $newCurrentParameters[$i] = $adjacent;
                        $best = min($best, $strokesToMoveNextParameter + 1 + transform($newCurrentParameters, $targetParameters));
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
