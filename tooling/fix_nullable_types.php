<?php
$dir = $argv[1] ?? '/home/shomsy/projects/avax/components';
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
);
$count = 0;
foreach ($iterator as $file) {
    if ($file->getExtension() !== 'php') continue;
    $path = $file->getPathname();
    $content = file_get_contents($path);
    // Fix : ?Type (return types)
    $fixed = preg_replace('/:\s*\?([A-Z][a-zA-Z0-9_]*)/', ': $1|null', $content);
    // Fix ?Type $param (parameter types)
    $fixed = preg_replace('/\s\?([A-Z][a-zA-Z0-9_]*)\s+\$/', ' $1|null $', $fixed);
    // Fix (?Type $param) in closure/constructor
    $fixed = preg_replace('/\(\?([A-Z][a-zA-Z0-9_]*)\s+\$/', '($1|null $', $fixed);
    // Fix ,?Type $param
    $fixed = preg_replace('/,\s*\?([A-Z][a-zA-Z0-9_]*)\s+\$/', ', $1|null $', $fixed);
    if ($fixed !== $content) {
        file_put_contents($path, $fixed);
        $count++;
    }
}
echo "Fixed $count files\n";
