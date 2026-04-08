<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Dependencies\Resolution;

use Avax\Container\DependencyInjection\Dependencies\Bindings\ServiceRegistration;
use Avax\Container\DependencyInjection\Scopes\Lifetimes\ScopedLifetime;
use Avax\Container\DependencyInjection\Scopes\Lifetimes\SharedLifetime;
use Avax\Container\DependencyInjection\Scopes\Lifetimes\TransientLifetime;

/**
 * One explicit lifetime decision for a resolved service.
 */
final readonly class LifetimePlan
{
    public function __construct(
        public string $serviceId,
        public string $name
    ) {}

    public static function fromRegistration(string $serviceId, ServiceRegistration|null $registration) : self
    {
        return new self(
            serviceId: $serviceId,
            name     : $registration?->lifetime ?? TransientLifetime::NAME
        );
    }

    /**
     * @param array<string, mixed> $state
     */
    public static function fromArray(string $serviceId, array $state) : self
    {
        return new self(
            serviceId: $serviceId,
            name     : (string) ($state['name'] ?? TransientLifetime::NAME)
        );
    }

    public function isShared() : bool
    {
        return $this->name === SharedLifetime::NAME;
    }

    public function isScoped() : bool
    {
        return $this->name === ScopedLifetime::NAME;
    }

    public function isTransient() : bool
    {
        return $this->name === TransientLifetime::NAME;
    }

    /**
     * @return array{name: string, shared: bool, scoped: bool, transient: bool}
     */
    public function toArray() : array
    {
        return [
            'name' => $this->name,
            'shared' => $this->isShared(),
            'scoped' => $this->isScoped(),
            'transient' => $this->isTransient(),
        ];
    }
}
