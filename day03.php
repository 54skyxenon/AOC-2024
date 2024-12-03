<?php
$filename = "inputs/day3.txt";
$lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

function evalMul(string $mulExpression): int {
    list($l, $r) = explode(',', substr($mulExpression, 4, -1));
    return intval($l) * intval($r);
}

function sumLine(string $line): int {
    preg_match_all('/mul\(\d+,\d+\)/', $line, $matches);
    return array_sum(array_map('evalMul', $matches[0]));
}

function getInstructionsForLine(string $line): array {
    preg_match_all('/mul\(\d+,\d+\)|do\(\)|don\'t\(\)/', $line, $matches);
    return $matches[0];
}

function processInstruction(array $carry, string $instruction): array {
    list($sum, $enabled) = $carry;
    return match (true) {
        str_starts_with($instruction, 'mul(') => [$sum + ($enabled ? evalMul($instruction) : 0), $enabled],
        str_starts_with($instruction, 'do(') => [$sum, true],
        str_starts_with($instruction, 'don\'t(') => [$sum, false],
    };
}

$part1 = array_sum(array_map('sumLine', $lines));
echo $part1 . "\n";

$allInstructions = array_merge(...array_map('getInstructionsForLine', $lines));
$part2 = array_reduce($allInstructions, 'processInstruction', [0, true])[0];
echo $part2 . "\n";
?>