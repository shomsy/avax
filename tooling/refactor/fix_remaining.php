<?php
$base = '/home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/';

// 1. Fix operator class constructor PHPDoc - the regex didn't work because of indentation
$operatorFiles = [
    'Aggregate/AverageValues.php',
    'Aggregate/FindMaxValue.php',
    'Aggregate/FindMinValue.php',
    'Aggregate/SumValues.php',
    'Search/ContainsValue.php',
    'Search/SearchValue.php',
    'Selection/HasValue.php',
    'Selection/ReadValueByPath.php',
];

foreach ($operatorFiles as $rel) {
    $f = $base . $rel;
    if (!file_exists($f)) continue;
    $c = file_get_contents($f);
    
    // The constructor uses promoted property: private array $items
    // Add PHPDoc before the constructor
    if (!str_contains($c, '@param array<array-key, mixed>')) {
        $c = preg_replace(
            '/(    public function __construct\(\n        )(private array \$items)/',
            "$1/** @param array<array-key, mixed> \$\$2 */\n        \$2",
            $c
        );
    }
    
    file_put_contents($f, $c);
    echo "Fixed constructor: $rel\n";
}

// 2. Fix Transform return types for methods that got missed
$transformFiles = glob($base . 'Transform/*.php');
foreach ($transformFiles as $f) {
    $c = file_get_contents($f);
    $name = basename($f, '.php');
    
    // Check for methods missing return PHPDoc
    $methods = ['__invoke', 'append', 'chunk', 'forget', 'partition', 'put', 'sort', 'sortBy', 'trim'];
    foreach ($methods as $method) {
        if (str_contains($c, "public function $method(") && !str_contains($c, "@return") && preg_match("/public function $method\([^)]*\): array/", $c)) {
            $c = preg_replace(
                "/(\n    )(public function $method\([^)]*\): array)/",
                "\$1/** @return array<array-key, mixed> */\n        \$2",
                $c,
                1
            );
        }
    }
    
    file_put_contents($f, $c);
}
echo "Fixed Transform return types\n";

// 3. Fix data structure @implements annotations
$structures = [
    'Map/Map.php' => 'array-key, mixed',
    'MultiMap/MultiMap.php' => 'array-key, mixed',
    'OrderedMap/OrderedMap.php' => 'array-key, mixed',
    'OrderedSet/OrderedSet.php' => 'int, mixed',
    'Sequence/Sequence.php' => 'int, mixed',
    'Set/Set.php' => 'int, mixed',
];

foreach ($structures as $rel => $types) {
    $f = $base . $rel;
    if (!file_exists($f)) continue;
    $c = file_get_contents($f);
    $cls = basename($f, '.php');
    
    // Add @implements to the docblock
    if (!str_contains($c, '@implements')) {
        $c = preg_replace(
            '/(\/\*\*\s*\n\s*\* [^\n]*\n\s*\*\/)\n(final readonly class ' . $cls . ')/',
            "/**\n * @implements IteratorAggregate<$types>\n */\n\$2",
            $c
        );
    }
    
    file_put_contents($f, $c);
    echo "Fixed @implements: $rel\n";
}

// 4. Fix DotPath issues
$f = $base . 'DataPaths/DotPath.php';
$c = file_get_contents($f);

// Fix getKey - array_pop on empty array returns false
$c = str_replace(
    "return end(\$segments);",
    "return \$segments !== [] ? end(\$segments) : '';",
    $c
);

// Actually let me read the actual getKey method first
echo "DotPath needs manual review\n";

echo "Done\n";
