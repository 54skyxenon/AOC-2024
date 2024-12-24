<?php
$filename = 'inputs/day24.txt';
$lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$results = [];
$expressions = [];
$variables = [];

// More accurate to compare these instead of the expanded string versions of the expressions
class AST
{
    public string $operator;
    public AST|string $left;
    public AST|string $right;

    public function __construct(string $operator, AST|string $left, AST|string $right)
    {
        $this->operator = $operator;
        $this->left = $left;
        $this->right = $right;
    }
}

function evaluate(string $variable): int
{
    global $results, $expressions;

    if (!array_key_exists($variable, $results)) {
        list($arg1, $bitwiseOperator, $arg2) = explode(' ', $expressions[$variable]);
        $results[$variable] = match ($bitwiseOperator) {
            'AND' => evaluate($arg1) & evaluate($arg2),
            'XOR' => evaluate($arg1) ^ evaluate($arg2),
            'OR' => evaluate($arg1) | evaluate($arg2),
        };
    }

    return $results[$variable];
}

// If the ASTs for the expected z[i] and actual z[i] match, we've fixed it (or it was never wrong originally)
function sameAST(AST|string $a, AST|string $b): bool
{
    if (gettype($a) === gettype($b)) {
        if (gettype($a) === 'string') {
            return $a === $b;
        } else {
            return $a->operator === $b->operator and ((sameAST($a->left, $b->left) and sameAST($a->right, $b->right)) or (sameAST($a->right, $b->left) and sameAST($a->right, $b->left)));
        }
    }

    return false;
}

function baseCaseVariable(string $variable): bool
{
    return str_starts_with($variable, 'x') or str_starts_with($variable, 'y');
}

/**
 * @link https://en.wikipedia.org/wiki/Adder_(electronics)#Ripple-carry_adder
 * 
 * Carry digit for ripple carry adder: carry_out[i] = ((x[i] ^ y[i]) & carry_out[i - 1]) | (x[i] & y[i]).
 * ! This does not rely on the actual contents of $expressions
 */
function expectedCarryOutFormula(int $index): AST
{
    if ($index === 0) {
        return new AST('AND', 'x00', 'y00');
    }

    $i = str_pad(strval($index), 2, '0', STR_PAD_LEFT);

    return new AST(
        'OR',
        new AST('AND', new AST('XOR', "x$i", "y$i"), expectedCarryOutFormula($index - 1)),
        new AST('AND', "x$i", "y$i"),
    );
}

/**
 * @link https://en.wikipedia.org/wiki/Adder_(electronics)#Ripple-carry_adder
 * 
 * Output digit for ripple carry adder: z[i] = (x[i] ^ y[i]) ^ carry_out[i - 1].
 * ! This does not rely on the actual contents of $expressions
 */
function expectedFormulaForZ(int $index): AST
{
    $i = str_pad(strval($index), 2, '0', STR_PAD_LEFT);

    if ($index === 0) {
        return new AST('XOR', "x$i", "y$i");
    }

    return new AST(
        'XOR',
        new AST('XOR', "x$i", "y$i"),
        expectedCarryOutFormula($index - 1),
    );
}

// Uses contents of $expressions to recursively construct the AST
function actualFormula(string $variable, array $seen): AST|string
{
    global $expressions;

    if (in_array($variable, $seen)) {
        return "(*cycle=$variable)";
    }

    if (baseCaseVariable($variable)) {
        return $variable;
    }

    list($lhs, $operator, $rhs) = explode(' ', $expressions[$variable]);
    return new AST($operator, actualFormula($lhs, [...$seen, $variable]), actualFormula($rhs, [...$seen, $variable]));
}

// Evaluate wires with z prefix
function solvePart1(): int
{
    global $variables;

    $values = [];
    foreach ($variables as $variable) {
        if (str_starts_with($variable, 'z')) {
            $index = intval(substr($variable, 1));
            $values[$index] = evaluate($variable);
        }
    }

    $bitString = '';
    for ($i = 0; $i < count($values); $i++) {
        $bitString = $values[$i] . $bitString;
    }

    return bindec($bitString);
}

function solvePart2(): string
{
    global $variables, $expressions;

    $numZ = 0;
    foreach ($variables as $variable) {
        if (str_starts_with($variable, 'z')) {
            $numZ++;
        }
    }

    $changed = [];
    for ($zi = 0; $zi < $numZ - 1; $zi++) {
        $i = str_pad(strval($zi), 2, '0', STR_PAD_LEFT);

        if (!sameAST(expectedFormulaForZ($zi), actualFormula("z$i", []))) {
            echo "Broke at z$i...";
            $originalExpressions = $expressions;

            $swaps = [];
            for ($first = 0; $first < count($variables) - 1; $first++) {
                for ($second = $first + 1; $second < count($variables); $second++) {
                    if (!baseCaseVariable($variables[$first]) and !baseCaseVariable($variables[$second])) {
                        $swaps[] = [$variables[$first], $variables[$second]];
                    }
                }
            }

            foreach ($swaps as list($a, $b)) {
                $temp = $expressions[$a];
                $expressions[$a] = $expressions[$b];
                $expressions[$b] = $temp;
                foreach (array_keys($expressions) as $key) {
                    $expressions[$key] = str_replace([$a, $b], [$b, $a], $expressions[$key]);
                }

                $fixed = true;
                for ($indexUpToI = 0; $indexUpToI <= $i; $indexUpToI++) {
                    $j = str_pad(strval($indexUpToI), 2, '0', STR_PAD_LEFT);
                    if (!sameAST(expectedFormulaForZ($j), actualFormula("z$j", []))) {
                        $fixed = false;
                        break;
                    }
                }

                // We don't revert the state if it was fixed
                if ($fixed) {
                    array_push($changed, $a, $b);
                    echo "fixed by swapping \e[1;37;42m$a\e[0m with \e[1;37;42m$b\e[0m!\n";
                    break;
                }

                $expressions = $originalExpressions;
            }
        }
    }

    sort($changed);
    return implode(',', $changed);
}

foreach ($lines as $line) {
    if (str_contains($line, ': ')) {
        list($lhs, $rhs) = explode(': ', $line);
        $results[$lhs] = intval($rhs);
        $variables[] = $lhs;
    } else {
        list($lhs, $rhs) = explode(' -> ', $line);
        $expressions[$rhs] = $lhs;
        array_push($variables, $rhs, explode(' ', $lhs)[0], explode(' ', $lhs)[2]);
    }
}
$variables = [...array_unique($variables)];

echo solvePart1() . "\n";
echo solvePart2() . "\n";
