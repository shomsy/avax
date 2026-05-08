<?php
$f = '/home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/DataPaths/DotPath.php';
$c = file_get_contents($f);

// Remove the broken duplicate method declarations
// Pattern: /** @return ... */\n    public function getSegments(): array\n    /** @return ... */\n    public function getSegments
$c = preg_replace(
    '/\/\*\* @return array<array-key, mixed> \*\/\n    public function getSegments\(\): array\n    \/\*\* @return array<array-key, mixed>\|void \*\/\n    public function getSegments\n    \{/',
    "/** @return list<string> */\n    public function getSegments(): array\n    {",
    $c
);

// Fix other methods that got corrupted
// Pattern: public function X(): array\n    /** @return ... */\n    public function X
$c = preg_replace(
    '/(public function \w+)\(\): array\n    \/\*\* @return [^\*]+\*\/\n    \1\n    \{/',
    '$1(): array' . "\n    {",
    $c
);

file_put_contents($f, $c);
echo "Fixed DotPath\n";
