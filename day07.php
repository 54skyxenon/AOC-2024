<?php
$filename = 'inputs/day7.txt';
$lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$part1Operations = [
    fn($a, $b) => $a + $b,
    fn($a, $b) => $a * $b
];
$part2Operations = [
    ...$part1Operations,
    fn($a, $b) => intval(strval($a) . strval($b))
];

function dfs(int $index, int $path, int $target, array &$nums, array &$operations): bool
{
    if ($index === count($nums)) {
        return $path === $target;
    }

    foreach ($operations as $operation) {
        if (dfs($index + 1, $operation($path, $nums[$index]), $target, $nums, $operations)) {
            return true;
        }
    }

    return false;
}

function getCalibrationResult(array &$lines, array &$operations): int
{
    $totalCalibrationResult = 0;

    foreach ($lines as $line) {
        list($lhs, $rhs) = explode(': ', $line);
        $lhs = intval($lhs);
        $rhs = array_map('intval', explode(' ', $rhs));
        if (dfs(1, $rhs[0], $lhs, $rhs, $operations)) {
            $totalCalibrationResult += $lhs;
        }
    }

    return $totalCalibrationResult;
}

echo getCalibrationResult($lines, $part1Operations) . "\n";
echo getCalibrationResult($lines, $part2Operations) . "\n";
