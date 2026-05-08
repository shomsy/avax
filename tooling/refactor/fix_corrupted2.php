<?php
// Fix remaining corrupted files
$files = [
    '/home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/Sequence/Sequence.php',
    '/home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/OrderedSet/OrderedSet.php',
    '/home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/Map/Map.php',
    '/home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/MultiMap/MultiMap.php',
    '/home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/OrderedMap/OrderedMap.php',
];

foreach ($files as $f) {
    if (!file_exists($f)) continue;
    $c = file_get_contents($f);
    
    // Fix the literal \n sequences
    // Pattern: */\n * @implements...\n */\nfinal readonly class
    $c = preg_replace('/\*\/\\\\n \* @implements IteratorAggregate<[^>]+>\\\\n \*\/\\\\nfinal readonly class (\w+) implements/', "*/\nfinal readonly class \$1 implements", $c);
    
    file_put_contents($f, $c);
    echo "Fixed: " . basename(dirname($f)) . '/' . basename($f) . "\n";
}
