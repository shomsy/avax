<?php
// Fix PartitionValues - remove duplicate PHPDoc
$f = '/home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/Transform/PartitionValues.php';
$c = file_get_contents($f);

// Remove duplicate PHPDoc before __invoke
$c = preg_replace(
    '/\/\*\*\s*\n\s*\* @param\s+callable.*?\*\/\s*\n\s*\/\*\* @return [^\*]+\*\/\s*\n(    public function __invoke)/',
    "/**\n     * @param callable(mixed): bool \$callback\n     * @return array{array, array}\n     */\n\$1",
    $c
);

// Remove duplicate PHPDoc before partition  
$c = preg_replace(
    '/\/\*\*\s*\n\s*\* @param\s+callable.*?\*\/\s*\n\s*\/\*\* @return [^\*]+\*\/\s*\n(    public function partition)/',
    "/**\n     * @param callable(mixed): bool \$callback\n     * @return array{array, array}\n     */\n\$1",
    $c
);

file_put_contents($f, $c);
echo "Fixed PartitionValues\n";

// Fix MaxValue/MinValue - array_values still returns array<mixed>
// The issue is PHPStan doesn't know the array is non-empty after the check
// Use @phpstan-assert or just add explicit type narrowing
$f = '/home/shomsy/projects/avax/components/DataStack/Data/System/Flows/Aggregate/MaxValue.php';
$c = file_get_contents($f);
// The cleanest fix: use array_filter with a type-narrowing callback
$c = str_replace(
    "return \$values === [] ? null : max(array_values(\$values));",
    "/** @var non-empty-array \$values */\n        return max(\$values);",
    $c
);
file_put_contents($f, $c);

$f = '/home/shomsy/projects/avax/components/DataStack/Data/System/Flows/Aggregate/MinValue.php';
$c = file_get_contents($f);
$c = str_replace(
    "return \$values === [] ? null : min(array_values(\$values));",
    "/** @var non-empty-array \$values */\n        return min(\$values);",
    $c
);
file_put_contents($f, $c);
echo "Fixed MaxValue/MinValue\n";

// Fix PublicSurface collect - the generics issue is from CollectionInterface
$f = '/home/shomsy/projects/avax/components/DataStack/Data/System/PublicSurface/Data.php';
$c = file_get_contents($f);
// Add proper PHPDoc
$c = preg_replace(
    '/(    )(public function collect\(array )/',
    "$1/** @param array<array-key, mixed> \$items\n     * @return Collection<array-key, mixed> */\n    $2",
    $c
);
file_put_contents($f, $c);
echo "Fixed PublicSurface\n";

echo "Done\n";
