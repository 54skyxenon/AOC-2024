<?php
$filename = 'inputs/day4.txt';
$lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

function works(array $letters)
{
    return in_array(implode('', $letters), ['XMAS', 'SAMX']);
}

function worksMAS(array $letters)
{
    return in_array(implode('', $letters), ['MAS', 'SAM']);
}

function solvePart1(array $lines)
{
    $ans = 0;
    $n = count($lines);
    $m = strlen($lines[0]);

    for ($row = 0; $row < $n; $row++) {
        for ($col = 0; $col < $m; $col++) {
            # Horizontal
            if ($col + 3 < $m) {
                $ans += works([$lines[$row][$col], $lines[$row][$col + 1], $lines[$row][$col + 2], $lines[$row][$col + 3]]);
            }

            # Vertical
            if ($row + 3 < $n) {
                $ans += works([$lines[$row][$col], $lines[$row + 1][$col], $lines[$row + 2][$col], $lines[$row + 3][$col]]);
            }

            # Diagonal \
            if ($row >= 3 and $col >= 3) {
                $ans += works([$lines[$row][$col], $lines[$row - 1][$col - 1], $lines[$row - 2][$col - 2], $lines[$row - 3][$col - 3]]);
            }

            # Diagonal /
            if ($row >= 3 and $col + 3 < $m) {
                $ans += works([$lines[$row][$col], $lines[$row - 1][$col + 1], $lines[$row - 2][$col + 2], $lines[$row - 3][$col + 3]]);
            }
        }
    }

    return $ans;
}

function solvePart2(array $lines)
{
    $ans = 0;
    $n = count($lines);
    $m = strlen($lines[0]);

    for ($row = 0; $row < $n; $row++) {
        for ($col = 0; $col < $m; $col++) {
            if ($row + 2 < $n and $col + 2 < $m) {
                # Diagonal \ and / both need to work
                $worksBackwardsSlash = worksMAS([$lines[$row][$col], $lines[$row + 1][$col + 1], $lines[$row + 2][$col + 2]]);
                $worksForwardsSlash = worksMAS([$lines[$row + 2][$col], $lines[$row + 1][$col + 1], $lines[$row][$col + 2]]);
                $ans += ($worksBackwardsSlash and $worksForwardsSlash);
            }
        }
    }

    return $ans;
}

echo solvePart1($lines) . "\n";
echo solvePart2($lines) . "\n";
