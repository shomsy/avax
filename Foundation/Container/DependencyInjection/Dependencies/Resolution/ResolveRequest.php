<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Dependencies\Resolution;

final class ResolveRequest
{
    /**
     * @param array<string, mixed> $overrides
     */
    public function __construct(
        public readonly string $serviceId,
        public readonly array $overrides = [],
        public readonly array $context = [],
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
            context  : $this->context,
            parent   : $this,
            manualInjection: $this->manualInjection,
            consumer : $this->serviceId
        );
    }

    /**
     * @param array<string, mixed> $context
     */
    public function withContext(array $context) : self
    {
        return new self(
            serviceId : $this->serviceId,
            overrides : $this->overrides,
            context   : $context,
            parent    : $this->parent,
            manualInjection: $this->manualInjection,
            consumer  : $this->consumer
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
