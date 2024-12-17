<?php
$filename = 'inputs/day17.txt';
$lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

enum OperandMode
{
    case COMBO;
    case LITERAL;
}

function getOperandValue(array &$registers, int $operand, OperandMode $operandMode): int
{
    if ($operandMode === OperandMode::LITERAL) {
        return $operand;
    }

    // Service combo operand
    if (0 <= $operand && $operand <= 3) {
        return $operand;
    } elseif ($operand === 4) {
        return $registers['A'];
    } elseif ($operand === 5) {
        return $registers['B'];
    } elseif ($operand === 6) {
        return $registers['C'];
    } else {
        throw new DomainException("Invalid combo operand: $operand");
    }
}

function instructionDump(string $fmtString): void
{
    $DEBUG = false;
    if ($DEBUG) {
        echo str_replace(["combo(0)", "combo(1)", "combo(2)", "combo(3)", "combo(4)", "combo(5)", "combo(6)"], ["0", "1", "2", "3", "A", "B", "C"], $fmtString);
    }
}

function executeProgram(array $registers, array &$program): array
{
    $output = [];
    $ptr = 0;

    while ($ptr < count($program)) {
        $opcode = $program[$ptr];
        $operand = $program[$ptr + 1];

        switch ($opcode) {
            case 0: // adv
                instructionDump("A = A >> combo($operand)\n");
                $registers['A'] = intdiv($registers['A'], 2 ** getOperandValue($registers, $operand, OperandMode::COMBO));
                break;
            case 1: // bxl
                instructionDump("B ^= $operand\n");
                $registers['B'] ^= getOperandValue($registers, $operand, OperandMode::LITERAL);
                break;
            case 2: // bst
                instructionDump("B = combo($operand) % 8\n");
                $registers['B'] = getOperandValue($registers, $operand, OperandMode::COMBO) % 8;
                break;
            case 3: // jnz
                instructionDump("if (A > 0) repeat\n");
                if ($registers['A'] !== 0) {
                    $ptr = getOperandValue($registers, $operand, OperandMode::LITERAL) - 2;
                }
                break;
            case 4: // bxc
                instructionDump("B ^= C\n");
                $registers['B'] ^= $registers['C'];
                break;
            case 5: // out
                instructionDump("print(combo($operand) % 8)\n");
                $output[] = getOperandValue($registers, $operand, OperandMode::COMBO) % 8;
                break;
            case 6: // bdv
                instructionDump("B = A >> combo($operand)\n");
                $registers['B'] = intdiv($registers['A'], 2 ** getOperandValue($registers, $operand, OperandMode::COMBO));
                break;
            case 7: // cdv
                instructionDump("C = A >> combo($operand)\n");
                $registers['C'] = intdiv($registers['A'], 2 ** getOperandValue($registers, $operand, OperandMode::COMBO));
                break;
            default:
                throw new DomainException("Invalid opcode: $opcode");
        }

        $ptr += 2;
    }

    return $output;
}

// Needs to be hand reverse-engineered from your custom input
function calculate(int $a): int
{
    $b = $a % 8;
    $b ^= 2;
    $c = $a >> $b;
    $b ^= $c;
    $a >>= 3;
    $b ^= 7;
    return $b % 8;
}

// Genius solution: https://www.reddit.com/r/adventofcode/comments/1hg38ah/comment/m2gq8px/
function dfs(int $a, int $depth, array &$program): int|null
{
    if ($depth === count($program)) {
        return $a;
    }

    for ($mask = 0; $mask < 8; $mask++) {
        $nextA = ($a << 3) | $mask;
        if (calculate($nextA) === array_reverse($program)[$depth]) {
            $result = dfs($nextA, $depth + 1, $program);
            if ($result !== null) {
                return $result;
            }
        }
    }

    return null;
}

$initialRegisters = [];
$program = null;

foreach ($lines as $line) {
    $tokens = explode(' ', $line);
    if (str_contains($line, 'Register')) {
        $initialRegisters[$tokens[1][0]] = intval(end($tokens));
    } else {
        $program = array_map('intval', explode(',', end($tokens)));
    }
}

$part1 = implode(',', executeProgram($initialRegisters, $program));
$part2 = dfs(0, 0, $program);

assert(executeProgram(['A' => $part2, 'B' => 0, 'C' => 0], $program) === $program);

echo $part1 . "\n";
echo $part2 . "\n";
