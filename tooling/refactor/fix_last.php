<?php
$base = '/home/shomsy/projects/avax/components/DataStack/Data/System/';

// Fix Collection count() - add @return int<0, max>
$f = $base . 'Capabilities/Collection/Collection.php';
$c = file_get_contents($f);
// Check if we already added PHPDoc
if (!str_contains($c, '@return int<0, max>')) {
    $c = str_replace(
        "    public function count(): int",
        "    /** @return int<0, max> */\n    public function count(): int",
        $c
    );
    file_put_contents($f, $c);
    echo "Fixed Collection count\n";
}

// Fix Set union/intersect/diff - add iterable type annotation
$f = $base . 'Capabilities/Set/Set.php';
$c = file_get_contents($f);
$c = str_replace(
    "public function union(iterable \$items): self",
    "/** @param iterable<mixed> \$items */\n    public function union(iterable \$items): self",
    $c
);
$c = str_replace(
    "public function intersect(iterable \$items): self",
    "/** @param iterable<mixed> \$items */\n    public function intersect(iterable \$items): self",
    $c
);
$c = str_replace(
    "public function diff(iterable \$items): self",
    "/** @param iterable<mixed> \$items */\n    public function diff(iterable \$items): self",
    $c
);
file_put_contents($f, $c);
echo "Fixed Set iterable types\n";

// Fix Flow MaxValue/MinValue - non-empty-array issue
$f = $base . 'Flows/Aggregate/MaxValue.php';
$c = file_get_contents($f);
$c = str_replace(
    "return max(...\$items);",
    "return \$items !== [] ? max(...\$items) : throw new \\InvalidArgumentException('Cannot get max of empty array.');",
    $c
);
file_put_contents($f, $c);
echo "Fixed MaxValue\n";

$f = $base . 'Flows/Aggregate/MinValue.php';
$c = file_get_contents($f);
$c = str_replace(
    "return min(...\$items);",
    "return \$items !== [] ? min(...\$items) : throw new \\InvalidArgumentException('Cannot get min of empty array.');",
    $c
);
file_put_contents($f, $c);
echo "Fixed MinValue\n";

// Fix PublicSurface collect() generics issue
$f = $base . 'PublicSurface/Data.php';
$c = file_get_contents($f);
// The issue is the return type has generics but the actual return doesn't match
// Let's see what the method looks like
if (str_contains($c, 'public function collect')) {
    echo "PublicSurface collect needs review\n";
}

echo "Done\n";
