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
    if (! $file) {
        continue;
    }
    $content = file_get_contents($file);
    $changed = false;

    // Fix PublicSurface\Container to System\Container
    if (str_contains($content, 'Avax\Components\Application\Container\System\PublicSurface\Container')) {
        $content = str_replace(
            'Avax\Components\Application\Container\System\PublicSurface\Container',
            'Avax\Components\Application\Container\System\Container',
            $content
        );
        $changed = true;
    }

    // Also fix any remaining DI\ references just in case
    if (str_contains($content, 'Avax\Components\Application\Container\DI\\')) {
        $content = str_replace(
            'Avax\Components\Application\Container\DI\\',
            'Avax\Components\Application\Container\System\\',
            $content
        );
        $changed = true;
    }

    if ($changed) {
        file_put_contents($file, $content);
        echo "Fixed namespace in $file\n";
        $count++;
    }
}
echo "Fixed $count files.\n";
