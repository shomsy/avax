<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/System',
        __DIR__ . '/examples',
        __DIR__ . '/integrations',
        __DIR__ . '/tests',
        __DIR__ . '/tooling',
    ])
    // uncomment to reach your current PHP version
    // ->withPhpSets()
    ->withTypeCoverageLevel(0);
