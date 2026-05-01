<?php

declare(strict_types=1);

namespace Avax\Framework\System\Configuration\RegisterComponents;

use Avax\Framework\System\Capabilities\ComponentRegistry\ComponentDefinition;
use Avax\Framework\System\Capabilities\ComponentRegistry\ComponentProviderInterface;
use Avax\Framework\System\Capabilities\ComponentRegistry\ComponentRegistry;

final readonly class ComponentRegistration
{
    public function __construct(
        private ComponentRegistry $componentRegistry,
    ) {
    }

    public function register(ComponentDefinition $componentDefinition) : void
    {
        $this->componentRegistry->register(definition: $componentDefinition);
    }

    public function registerProvider(ComponentProviderInterface $componentProvider) : void
    {
        $this->componentRegistry->registerProvider(provider: $componentProvider);
    }

    /**
     * @return list<ComponentDefinition>
     */
    public function getRegistered(): array
    {
        return $this->componentRegistry->all();
    }
}
