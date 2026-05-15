<?php

declare(strict_types=1);

namespace Avax\Components\Presentation\View\System\Configuration\Builders;

use Avax\Components\Presentation\View\System\Capabilities\Engines\TemplateEngineInterface;
use Avax\Components\Presentation\View\System\PublicSurface\View;

final class RegisterViewDependencies
{
    /**
     * @param array<string, mixed> $config
     */
    public static function register(
        TemplateEngineInterface $templateEngine,
        array $config = [],
    ): void {
        $defaults = [
            'path' => base_path('resources/views'),
            'cache_path' => base_path('storage/views'),
            'extension' => '.php',
        ];

        $config = array_merge($defaults, $config);

        // View::setEngine($templateEngine);
        // View::configure($config);
    }
}
