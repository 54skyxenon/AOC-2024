<?php

require_once __DIR__ . '/vendor/autoload.php';
error_reporting(E_ALL ^ E_DEPRECATED);

use MathPHP\LinearAlgebra\MatrixFactory;
use MathPHP\LinearAlgebra\Vector;

$filename = 'inputs/day13.txt';
$lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

function coefficients(string $line): array
{
    preg_match_all('/\d+/', $line, $matches);
    return array_map('intval', $matches[0]);
}

function basicallyAnInt(int|float $x): bool
{
    return abs($x - round($x)) < 0.0001;
}

function solve(array &$lines, int $shift = 0): int|float
{
    $ans = 0;

    for ($i = 0; $i < count($lines); $i += 3) {
        list($a1, $b1) = coefficients($lines[$i]);
        list($a2, $b2) = coefficients($lines[$i + 1]);
        list($c1, $c2) = coefficients($lines[$i + 2]);

        $A = MatrixFactory::create([
            [$a1, $a2],
            [$b1, $b2],
        ]);
        $b = new Vector([$c1 + $shift, $c2 + $shift]);
        $x = $A->solve($b);

        if (basicallyAnInt($x[0]) and basicallyAnInt($x[1])) {
            $ans += $x[0] * 3 + $x[1];
        }
    }

    return $ans;
}

echo solve($lines) . "\n";
echo solve($lines, 10000000000000) . "\n";
