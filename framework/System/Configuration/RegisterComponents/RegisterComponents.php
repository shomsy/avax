<?php

declare(strict_types=1);

namespace Avax\Framework\System\Configuration\RegisterComponents;

use Avax\Framework\System\Capabilities\ComponentRegistry\ComponentDefinition;

final class RegisterComponents
{
    /**
     * @var list<ComponentDefinition>
     */
    private array $registrations = [];

    public function register(ComponentDefinition $componentDefinition): self
    {
        $this->registrations[] = $componentDefinition;

        return $this;
    }

    /**
     * @return list<ComponentDefinition>
     */
    public function getRegistrations(): array
    {
        return $this->registrations;
    }
}
