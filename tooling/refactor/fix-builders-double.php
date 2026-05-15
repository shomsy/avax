<?php
$directories = [dirname(__DIR__, 2) . '/components', dirname(__DIR__, 2) . '/framework', dirname(__DIR__, 2) . '/tests'];
function fixFiles($dir) {
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . '/' . $item;
        if (is_dir($path)) fixFiles($path);
        elseif (is_file($path) && str_ends_with($path, '.php')) {
            $content = file_get_contents($path);
            if (str_contains($content, 'Builders\\Builders')) {
                file_put_contents($path, str_replace('Builders\\Builders', 'Builders', $content));
                echo "Fixed $path\n";
            }
        }
    }
}
foreach ($directories as $dir) fixFiles($dir);
