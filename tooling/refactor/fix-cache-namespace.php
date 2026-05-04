<?php

$dirs = ['components', 'tests', 'framework'];
$files = [];
foreach ($dirs as $dir) {
    if (is_dir($dir)) {
        $out = trim(shell_exec("find $dir -type f -name \"*.php\""));
        if ($out) {
            $files = array_merge($files, explode("\n", $out));
        }
    }
}

$count = 0;
foreach ($files as $file) {
    if (!$file) continue;
    $content = file_get_contents($file);
    if (str_contains($content, 'Avax\Components\Cache\\') || str_contains($content, 'Avax\Components\Cache;')) {
        $content = str_replace(
            ['Avax\Components\Cache\\', 'Avax\Components\Cache;'], 
            ['Avax\Components\Application\Cache\\', 'Avax\Components\Application\Cache;'], 
            $content
        );
        file_put_contents($file, $content);
        echo "Fixed namespace in $file\n";
        $count++;
    }
}
echo "Fixed $count files.\n";
