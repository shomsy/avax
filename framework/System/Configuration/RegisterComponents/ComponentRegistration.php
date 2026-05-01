<?php

declare(strict_types=1);

namespace Avax\Framework\System\Configuration\RegisterComponents;

use Avax\Framework\System\Capabilities\ComponentRegistry\ComponentDefinition;
use Avax\Framework\System\Capabilities\ComponentRegistry\ComponentProviderInterface;
use Avax\Framework\System\Capabilities\ComponentRegistry\ComponentRegistry;

final class ComponentRegistration
{
    public function __construct(
        private readonly ComponentRegistry $registry,
    ) {
    }

    public function register(ComponentDefinition $definition): void
    {
        $this->registry->register(definition: $definition);
    }

    public function registerProvider(ComponentProviderInterface $provider): void
    {
        $this->registry->registerProvider(provider: $provider);
    }

    /**
     * @return list<ComponentDefinition>
     */
    public function getRegistered(): array
    {
        return $this->registry->all();
    }
}
