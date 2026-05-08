<?php
$base = '/home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/';

// Fix __invoke return types - these need explicit @return since PHPStan level requires it
$files = [
    'Transform/AppendValue.php' => ['__invoke' => 'array<array-key, mixed>'],
    'Transform/ChunkValues.php' => ['__invoke' => 'array<array-key, mixed>'],
    'Transform/ForgetValue.php' => ['__invoke' => 'array<array-key, mixed>'],
    'Transform/PartitionValues.php' => ['__invoke' => 'array{array, array}'],
    'Transform/PutValueByPath.php' => ['__invoke' => 'array<array-key, mixed>'],
    'Transform/SortValues.php' => ['__invoke' => 'array<array-key, mixed>'],
    'Transform/SortValuesBy.php' => ['__invoke' => 'array<array-key, mixed>'],
    'Transform/TrimValues.php' => ['__invoke' => 'array<array-key, mixed>'],
];

foreach ($files as $rel => $methods) {
    $f = $base . $rel;
    if (!file_exists($f)) continue;
    $c = file_get_contents($f);
    
    foreach ($methods as $method => $returnType) {
        // Check if there is already a PHPDoc comment before this method
        $pattern = '/(    )(public function ' . $method . '\()/';
        if (preg_match($pattern, $c, $matches)) {
            // Check if there is already a PHPDoc before it
            $phpdocPattern = '/\/\*\*[^*]*\*\/\s*public function ' . $method . '/';
            if (!preg_match($phpdocPattern, $c)) {
                $c = preg_replace(
                    $pattern,
                    '$1/** @return ' . $returnType . ' */' . "\n    $2",
                    $c
                );
            }
        }
    }
    
    file_put_contents($f, $c);
    echo "Fixed $rel\n";
}

// Fix MaxValue/MinValue - the spread operator on empty array issue
// The issue is array_filter returns array<mixed> not non-empty-array
// Fix by using a direct check
$f = $base . '../Flows/Aggregate/MaxValue.php';
$c = file_get_contents($f);
$c = str_replace(
    "return \$values === [] ? null : max(...\$values);",
    "return \$values === [] ? null : max(array_values(\$values));",
    $c
);
file_put_contents($f, $c);

$f = $base . '../Flows/Aggregate/MinValue.php';
$c = file_get_contents($f);
$c = str_replace(
    "return \$values === [] ? null : min(...\$values);",
    "return \$values === [] ? null : min(array_values(\$values));",
    $c
);
file_put_contents($f, $c);
echo "Fixed MaxValue/MinValue\n";

// Fix PublicSurface collect
$f = $base . 'PublicSurface/Data.php';
$c = file_get_contents($f);
// Remove the @return we added and just use plain return type
$c = preg_replace(
    '/\/\*\* @return Collection<array-key, mixed> \*\/\n(    public function collect)/',
    '$1',
    $c
);
file_put_contents($f, $c);
echo "Fixed PublicSurface\n";

echo "Done\n";
