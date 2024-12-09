<?php
$filename = 'inputs/day9.txt';
$lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

enum Format
{
    case FILE;
    case FREE_SPACE;
};

function blockLayout(string $diskMap): array
{
    $blocks = [];
    $formatState = Format::FILE;
    $id = 0;

    foreach (array_map('intval', str_split($diskMap)) as $digit) {
        if ($formatState === Format::FILE) {
            for ($i = 0; $i < $digit; $i++) {
                $blocks[] = strval($id);
            }
            $id++;
        } else {
            for ($i = 0; $i < $digit; $i++) {
                $blocks[] = '.';
            }
        }
        $formatState = ($formatState === Format::FILE) ? Format::FREE_SPACE : Format::FILE;
    }

    return $blocks;
}

function groupby(array $blocks): array
{
    $groups = [];
    $currentGroup = [];

    foreach ($blocks as $block) {
        if (empty($currentGroup) or end($currentGroup) === $block) {
            $currentGroup[] = $block;
        } else {
            $groups[] = $currentGroup;
            $currentGroup = [$block];
        }
    }

    $groups[] = $currentGroup;
    return $groups;
}

function checksum(array $blocks): int
{
    $checksum = 0;

    for ($i = 0; $i < count($blocks); $i++) {
        if ($blocks[$i] === '.') {
            continue;
        }
        $checksum += $i * intval($blocks[$i]);
    }

    return $checksum;
}

function solvePart1(string $diskMap): int
{
    $blocks = blockLayout($diskMap);

    $l = 0;
    $r = count($blocks) - 1;
    while ($l < $r) {
        if ($blocks[$l] !== '.') {
            $l++;
        } elseif ($blocks[$r] === '.') {
            $r--;
        } else {
            $tmp = $blocks[$l];
            $blocks[$l] = $blocks[$r];
            $blocks[$r] = $tmp;
        }
    }

    return checksum($blocks);
}

function solvePart2(string $diskMap): int
{
    $blocks = blockLayout($diskMap);
    $blockGroups = groupby($blocks);
    $defragmentedBlocks = [];

    while (!empty($blockGroups)) {
        // ~20 minutes, grab a snack or something
        if (count($blockGroups) % 100 == 0) {
            echo "Blocks left to process: " . count($blockGroups) . "\n";
        }

        $front = array_shift($blockGroups);

        if ($front[0] === '.') {
            $tail = [];
            $wasFitIn = false;

            while (!empty($blockGroups)) {
                // Look at the block group at the end
                $endBlockGroup = array_pop($blockGroups);

                // If it's free space or too big, skip it
                if ($endBlockGroup[0] === '.' or count($endBlockGroup) > count($front)) {
                    array_unshift($tail, $endBlockGroup);
                }
                // Otherwise, we've got to service it
                else {
                    $blocksNeeded = count($endBlockGroup);

                    // Replace with free space when pulling over to here
                    array_unshift($tail, array_fill(0, $blocksNeeded, '.'));

                    for ($i = 0; $i < $blocksNeeded; $i++) {
                        array_pop($front);
                    }

                    if (!empty($front)) {
                        array_unshift($blockGroups, $front);
                    }

                    $wasFitIn = true;
                    array_push($defragmentedBlocks, ...$endBlockGroup);
                    break;
                }
            }

            if (!$wasFitIn) {
                array_push($defragmentedBlocks, ...$front);
            }
            array_push($blockGroups, ...$tail);
        } else {
            array_push($defragmentedBlocks, ...$front);
        }
    }

    return checksum($defragmentedBlocks);
}

echo solvePart1($lines[0]) . "\n";
echo solvePart2($lines[0]) . "\n";
