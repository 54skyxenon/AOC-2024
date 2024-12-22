<?php
$filename = 'inputs/day22.txt';
$lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$ITERATIONS = 2000;

function mixThenPrune(int $secretNumber, int $givenNumber): int
{
    return ($secretNumber ^ $givenNumber) % 16777216;
}

// N + 1 length sequence of seed number followed by all future secret numbers
function generateSecretNumbers(int $seedNumber): array
{
    global $ITERATIONS;

    $processes = [
        fn($x) => $x * 64,
        fn($x) => intdiv($x, 32),
        fn($x) => $x * 2048,
    ];

    $secretNumbers = [$seedNumber];

    for ($i = 0; $i < $ITERATIONS; $i++) {
        $secretNumbers[] = array_reduce($processes, fn($carry, $process) => mixThenPrune($carry, $process($carry)), end($secretNumbers));
    }

    return $secretNumbers;
}

function nthSecretNumber(int $seedNumber): int
{
    $secretNumberSequence = generateSecretNumbers($seedNumber);
    return end($secretNumberSequence);
}

function nthSecretNumbersSum(array &$lines): int
{
    return array_sum(array_map(fn($line) => nthSecretNumber(intval($line)), $lines));
}

function priceHistory(int $seedNumber): array
{
    $secretNumberSequence = generateSecretNumbers($seedNumber);
    return array_map(fn($x) => $x % 10, $secretNumberSequence);
}

function optimalSell(array &$lines): int
{
    $changeSequenceProfits = [];

    // Maintain profit sum dictionary for first occurrence of sequence across all starting secret numbers
    foreach ($lines as $line) {
        $prices = priceHistory($line);
        $changeSequenceProfitsHere = [];

        for ($i = 4; $i < count($prices); $i++) {
            $changeSequence = [];
            for ($j = $i - 3; $j <= $i; $j++) {
                $changeSequence[] = $prices[$j] - $prices[$j - 1];
            }

            $changeSequenceKey = implode(',', $changeSequence);
            if (!array_key_exists($changeSequenceKey, $changeSequenceProfitsHere)) {
                $changeSequenceProfitsHere[$changeSequenceKey] = $prices[$i] % 10;
            }
        }

        foreach ($changeSequenceProfitsHere as $changeSequenceKey => $changeSequenceProfit) {
            $changeSequenceProfits[$changeSequenceKey] = ($changeSequenceProfits[$changeSequenceKey] ?? 0) + $changeSequenceProfit;
        }
    }

    return max($changeSequenceProfits);
}

echo nthSecretNumbersSum($lines) . "\n";
echo optimalSell($lines) . "\n";
