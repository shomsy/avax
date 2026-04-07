<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Resolution;

final class ResolveRequest
{
    /**
     * @param array<string, mixed> $overrides
     */
    public function __construct(
        public readonly string $serviceId,
        public readonly array $overrides = [],
        public readonly ResolveRequest|null $parent = null,
        public readonly bool $manualInjection = false,
        public readonly string|null $consumer = null
    ) {}

    /**
     * @param array<string, mixed> $overrides
     */
    public function child(string $serviceId, array $overrides = []) : self
    {
        return new self(
            serviceId: $serviceId,
            overrides: $overrides,
            parent   : $this,
            consumer : $this->serviceId
        );
    }

    public function contains(string $serviceId) : bool
    {
        $current = $this->parent;
        while ($current !== null) {
            if ($current->serviceId === $serviceId) {
                return true;
            }
            $current = $current->parent;
        }

        return false;
    }

    public function getPath() : string
    {
        $path = $this->parent?->getPath() ?? '';

        return ($path !== '' ? $path . ' -> ' : '') . $this->serviceId;
    }
}
