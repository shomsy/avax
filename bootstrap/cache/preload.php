<?php

declare(strict_types=1);

$root  = dirname(__DIR__, 2);
$files = [
    $root . '/Foundation/cache/container_compiled.php',
    $root . '/Foundation/cache/reflector_meta.php',
];

foreach ($files as $file) {
    if (! is_file($file)) {
        continue;
    }

    if (function_exists('opcache_compile_file')) {
        opcache_compile_file($file);
        continue;
    }

    require_once $file;
}
