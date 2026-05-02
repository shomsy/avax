<?php

declare(strict_types=1);

namespace Avax\Framework\System\Configuration;

use Avax\Framework\System\Capabilities\ComponentRegistry\ComponentDefinition;
use Avax\Framework\System\Capabilities\ComponentRegistry\ComponentProviderInterface;
use Avax\Framework\System\Capabilities\ComponentRegistry\ComponentRegistry;
use Avax\Framework\System\Capabilities\Runtime\RuntimeInterface;
use Avax\Framework\System\Capabilities\RuntimeSafety\RuntimeSafety;
use Avax\Framework\System\Capabilities\StateReset\StateResetRegistry;

/**
 * Framework provider that registers core framework capabilities.
 */
final class FrameworkProvider implements ComponentProviderInterface
{
    public static function name() : string
    {
        return 'framework';
    }

    public function register(ComponentRegistry $componentRegistry) : void
    {
        $componentRegistry->register(
            new ComponentDefinition(
                name         : 'framework.runtime',
                providerClass: self::class,
            ),
        );

        $componentRegistry->register(
            new ComponentDefinition(
                name         : 'framework.safety',
                providerClass: self::class,
            ),
        );
    }

    public function boot(RuntimeInterface $runtime) : void
    {
        // RuntimeSafety is handled via the Runtime's constructor
    }
}
