<?php
// Fix PHPDoc placement for promoted properties - @param must be BEFORE the function declaration
$files = [
    '/home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/Aggregate/AverageValues.php',
    '/home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/Aggregate/FindMaxValue.php',
    '/home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/Aggregate/FindMinValue.php',
    '/home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/Aggregate/SumValues.php',
    '/home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/Search/ContainsValue.php',
    '/home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/Search/SearchValue.php',
    '/home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/Selection/HasValue.php',
    '/home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/Selection/ReadValueByPath.php',
];

foreach ($files as $f) {
    $c = file_get_contents($f);
    
    // Remove the misplaced @param inside the constructor
    $c = preg_replace(
        '/(    public function __construct\(\n        )\/\*\* @param array<array-key, mixed> \$items \*\/\n        (private array \$items)/',
        '$1$2',
        $c
    );
    
    // Add @param BEFORE the constructor
    $c = preg_replace(
        '/(    )(public function __construct\(\n        private array \$items)/',
        "$1/** @param array<array-key, mixed> \$items */\n    $2",
        $c
    );
    
    file_put_contents($f, $c);
    echo "Fixed: " . basename($f) . "\n";
}

// Fix Transform return types - same issue with PHPDoc placement
$transformFiles = [
    'Transform/AppendValue.php',
    'Transform/ChunkValues.php',
    'Transform/ForgetValue.php',
    'Transform/PartitionValues.php',
    'Transform/PutValueByPath.php',
    'Transform/SortValues.php',
    'Transform/SortValuesBy.php',
    'Transform/TrimValues.php',
];

$base = '/home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/';
foreach ($transformFiles as $rel) {
    $f = $base . $rel;
    if (!file_exists($f)) continue;
    $c = file_get_contents($f);
    
    // Fix __invoke return type
    $c = preg_replace(
        '/(    )(public function __invoke\(\): array)/',
        "$1/** @return array<array-key, mixed> */\n    $2",
        $c
    );
    
    // Fix other method return types
    $methods = ['append', 'chunk', 'forget', 'partition', 'put', 'sort', 'sortBy', 'trim'];
    foreach ($methods as $method) {
        $c = preg_replace(
            '/(    )(public function ' . preg_quote($method) . '\([^)]*\): array)/',
            "$1/** @return array<array-key, mixed> */\n    $2",
            $c
        );
    }
    
    file_put_contents($f, $c);
    echo "Fixed: $rel\n";
}

// Fix ConvertCollectionToXml arrayToXml param
$f = $base . 'Transform/ConvertCollectionToXml.php';
$c = file_get_contents($f);
$c = preg_replace(
    '/(    )(private function arrayToXml\(array )/',
    "$1/** @param array<array-key, mixed> \$data */\n    $2",
    $c
);
file_put_contents($f, $c);
echo "Fixed: ConvertCollectionToXml\n";

// Fix Collection count - PHPDoc placement
$f = $base . 'Collection/Collection.php';
$c = file_get_contents($f);
// Remove any existing @return before count
$c = preg_replace(
    '/\/\*\* @return int<0, max> \*\/\n(    public function count\(\): int)/',
    "$1",
    $c
);
// Actually the issue is that Arrhae->count() returns int, not int<0,max>
// We can use assert or just accept this as a level-specific warning
// For now, add an assertion
$c = str_replace(
    "return \$this->arrhae->count();",
    "\$count = \$this->arrhae->count();\n        assert(\$count >= 0);\n\n        return \$count;",
    $c
);
file_put_contents($f, $c);
echo "Fixed: Collection count\n";

// Fix MaxValue/MinValue - the fix we applied might have broken things
$f = '/home/shomsy/projects/avax/components/DataStack/Data/System/Flows/Aggregate/MaxValue.php';
$c = file_get_contents($f);
// The issue is max() needs non-empty-array. We need to check before calling.
// Revert to original and use array_values which PHPStan knows returns list
$c = str_replace(
    "return \$items !== [] ? max(...\$items) : throw new \\InvalidArgumentException('Cannot get max of empty array.');",
    "return max(\$items);",
    $c
);
// Use a safe approach
$c = str_replace(
    "public function execute(array \$items)",
    "public function execute(array \$items)\n    {\n        if (\$items === []) {\n            return null;\n        }",
    $c
);
// Actually let me just read the file first
echo "MaxValue needs review\n";

$f = '/home/shomsy/projects/avax/components/DataStack/Data/System/Flows/Aggregate/MinValue.php';
$c = file_get_contents($f);
$c = str_replace(
    "return \$items !== [] ? min(...\$items) : throw new \\InvalidArgumentException('Cannot get min of empty array.');",
    "return min(\$items);",
    $c
);
file_put_contents($f, $c);
echo "Reverted MinValue\n";

echo "Done\n";
