<?php

declare(strict_types=1);

namespace Avax\Components\Presentation\View\System\Configuration;

use Avax\Components\Presentation\View\System\Capabilities\TemplateEngineInterface;
use Avax\Components\Presentation\View\System\PublicSurface\View;

final class RegisterViewDependencies
{
    public static function register(
        TemplateEngineInterface $engine,
        array $config = [],
    ): void {
        $defaults = [
            'path' => base_path('resources/views'),
            'cache_path' => base_path('storage/views'),
            'extension' => '.php',
        ];

        $config = array_merge($defaults, $config);

        View::setEngine($engine);
        View::configure($config);
    }
}
