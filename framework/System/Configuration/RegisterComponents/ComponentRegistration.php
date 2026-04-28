<?php

declare(strict_types=1);

namespace Avax\Framework\System\Configuration\RegisterComponents;

use Avax\Framework\System\Capabilities\ComponentRegistry\ComponentRegistry;

final class ComponentRegistration
{
    public function __construct(
        private readonly ComponentRegistry $registry,
    ) {
    }

    public function register(string $name, callable $provider): void
    {
        $this->registry->register($name, $provider);
    }

    public function getRegistered(): array
    {
        return $this->registry->all();
    }
}