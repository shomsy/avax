<?php

$files = [
    'components/Application/Config/System/PublicSurface/shortcuts.php',
    'components/Application/Config/functions.php',
    'components/Application/Config/System/Flows/LoadConfiguration/LoadConfiguration.php',
    'components/Application/Config/System/Capabilities/ConfigLoader/PHPArrayFileLoader.php',
];

foreach ($files as $file) {
    if (! file_exists($file)) {
        continue;
    }

    $content = file_get_contents($file);

    // Fix AppPath
    $content = str_replace(
        'Avax\Components\Application\Config\Architecture\DDD\AppPath',
        'Avax\Components\Application\Config\System\Capabilities\Architecture\AppPath',
        $content
    );

    // Fix Config service reference
    $content = str_replace(
        'Avax\Components\Application\Config\Service\Config',
        'Avax\Components\Application\Config\System\PublicSurface\Config',
        $content
    );

    // Fix ConfigLoaderInterface
    $content = str_replace(
        'Avax\Components\Application\Config\System\Capabilities\ConfigLoader\ConfigLoaderInterface',
        'Avax\Components\Application\Config\System\Capabilities\Loaders\ConfigLoaderInterface',
        $content
    );

    file_put_contents($file, $content);
    echo "Fixed $file\n";
}
