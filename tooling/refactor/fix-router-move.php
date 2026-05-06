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

    // Fix RouteCollection\RouteDefinition to RouteDefinition\RouteDefinition
    if (str_contains($content, 'Avax\Components\HTTP\Router\System\Capabilities\RouteCollection\RouteDefinition')) {
        $content = str_replace(
            'Avax\Components\HTTP\Router\System\Capabilities\RouteCollection\RouteDefinition',
            'Avax\Components\HTTP\Router\System\Capabilities\RouteDefinition\RouteDefinition',
            $content
        );
        $changed = true;
    }

    // Fix lowercase components
    if (str_contains($content, 'components\HTTP\Router\System\Capabilities\RouteDefinition\RouteDefinition')) {
        $content = str_replace(
            'components\HTTP\Router\System\Capabilities\RouteDefinition\RouteDefinition',
            'Avax\Components\HTTP\Router\System\Capabilities\RouteDefinition\RouteDefinition',
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
