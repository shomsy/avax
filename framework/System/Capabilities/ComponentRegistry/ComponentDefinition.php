<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\ComponentRegistry;

final readonly class ComponentDefinition
{
    public function __construct(
        private string $name,
        private string|null $providerClass = null,
    ) {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function providerClass(): string|null
    {
        return $this->providerClass;
    }
}
