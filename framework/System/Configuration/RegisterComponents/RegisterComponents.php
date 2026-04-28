<?php

declare(strict_types=1);

namespace Avax\Framework\System\Configuration\RegisterComponents;

final class RegisterComponents
{
    private array $registrations = [];

    public function register(string $name, callable $provider): self
    {
        $this->registrations[$name] = $provider;

        return $this;
    }

    public function getRegistrations(): array
    {
        return $this->registrations;
    }
}