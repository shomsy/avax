<?php

declare(strict_types=1);

namespace Infrastructure\Config;

use Avax\Config\Architecture\DDD\AppPath;

/**
 * Configuration array for paths used within the infrastructure.
 * Especially useful to keep paths consistent and centralized.
 *
 * The use of AppPath::getRoot() ensures that paths are resolved
 * dynamically based on the project's root directory, making the code
 * environment-independent.
 */
return [
    'views_path' => AppPath::getRoot().'Presentation/resources/views', // Dynamic path for views folder.
    'cache_path' => AppPath::VIEW_CACHE_PATH->get(),          // Dynamic path for cached templates.
    'assets' => '/Presentation/views/assets',              // Static folder path for view assets.
];
