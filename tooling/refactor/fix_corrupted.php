<?php
$files = [
    '/home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/Set/Set.php',
    '/home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/Sequence/Sequence.php',
    '/home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/OrderedSet/OrderedSet.php',
    '/home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/Map/Map.php',
    '/home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/MultiMap/MultiMap.php',
    '/home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/OrderedMap/OrderedMap.php',
    '/home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/DataList/DataList.php',
];

foreach ($files as $f) {
    if (!file_exists($f)) continue;
    $c = file_get_contents($f);
    
    // Fix literal \n strings in class declarations
    $patterns = [
        ['Set', 'int, mixed'],
        ['OrderedSet', 'int, mixed'],
        ['Sequence', 'array-key, mixed'],
        ['Map', 'array-key, mixed'],
        ['MultiMap', 'array-key, mixed'],
        ['OrderedMap', 'array-key, mixed'],
        ['DataList', 'int, mixed'],
    ];
    
    foreach ($patterns as [$cls, $types]) {
        $bad = "*/\\n * @implements IteratorAggregate<{$types}>\\n */\\nfinal readonly class {$cls} implements";
        $good = "*/\nfinal readonly class {$cls} implements";
        $c = str_replace($bad, $good, $c);
    }
    
    // Fix Set union/intersect/diff duplicate method lines
    $c = preg_replace(
        '/public function union\(iterable \/\*\*.*?public function union\(iterable \$union\): self/s',
        'public function union(iterable $union): self',
        $c
    );
    $c = preg_replace(
        '/public function intersect\(iterable \/\*\*.*?public function intersect\(iterable \$intersect\): self/s',
        'public function intersect(iterable $intersect): self',
        $c
    );
    $c = preg_replace(
        '/public function diff\(iterable \/\*\*.*?public function diff\(iterable \$diff\): self/s',
        'public function diff(iterable $diff): self',
        $c
    );
    
    file_put_contents($f, $c);
    echo "Fixed: " . basename(dirname($f)) . '/' . basename($f) . "\n";
}
