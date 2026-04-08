<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Dependencies\Resolution;

use Avax\Container\DependencyInjection\Dependencies\Bindings\ServiceRegistration;
use Avax\Container\DependencyInjection\Scopes\Lifetimes\JobLifetime;
use Avax\Container\DependencyInjection\Scopes\Lifetimes\OperationLifetime;
use Avax\Container\DependencyInjection\Scopes\Lifetimes\RequestLifetime;
use Avax\Container\DependencyInjection\Scopes\Lifetimes\ScopedLifetime;
use Avax\Container\DependencyInjection\Scopes\Lifetimes\SharedLifetime;
use Avax\Container\DependencyInjection\Scopes\Lifetimes\TenantLifetime;
use Avax\Container\DependencyInjection\Scopes\Lifetimes\TransientLifetime;
use Avax\Container\DependencyInjection\Scopes\ScopeKind;

/**
 * One explicit lifetime decision for a resolved service.
 */
final readonly class LifetimePlan
{
    public function __construct(
        public string $serviceId,
        public string $name,
        public string $storage,
        public string $scopeKind = '',
        public bool $warm = false,
        public bool $lazy = false,
        public bool $disposable = false
    ) {}

    public static function fromRegistration(string $serviceId, ServiceRegistration|null $registration) : self
    {
        $name = $registration?->lifetime ?? TransientLifetime::NAME;
        $storage = self::storageFor(name: $name);
        $scopeKind = self::scopeKindFor(name: $name);

        return new self(
            serviceId  : $serviceId,
            name       : $name,
            storage    : $storage,
            scopeKind  : $scopeKind,
            warm       : (bool) ($registration?->warm ?? false),
            lazy       : (bool) ($registration?->lazy ?? false),
            disposable : (bool) ($registration?->disposable ?? false)
        );
    }

    /**
     * @param array<string, mixed> $state
     */
    public static function fromArray(string $serviceId, array $state) : self
    {
        $name = (string) ($state['name'] ?? TransientLifetime::NAME);

        return new self(
            serviceId  : $serviceId,
            name       : $name,
            storage    : (string) ($state['storage'] ?? self::storageFor(name: $name)),
            scopeKind  : (string) ($state['scopeKind'] ?? self::scopeKindFor(name: $name)),
            warm       : (bool) ($state['warm'] ?? false),
            lazy       : (bool) ($state['lazy'] ?? false),
            disposable : (bool) ($state['disposable'] ?? false)
        );
    }

    public function isShared() : bool
    {
        return $this->storage === SharedLifetime::NAME;
    }

    public function isScoped() : bool
    {
        return $this->storage === ScopedLifetime::NAME;
    }

    public function isTransient() : bool
    {
        return $this->storage === TransientLifetime::NAME;
    }

    public function requiresScope() : bool
    {
        return $this->isScoped();
    }

    public function scopeKind() : string
    {
        return $this->scopeKind !== '' ? $this->scopeKind : ScopeKind::ANY;
    }

    /**
     * @return array{
     *     name: string,
     *     storage: string,
     *     scopeKind: string,
     *     shared: bool,
     *     scoped: bool,
     *     transient: bool,
     *     warm: bool,
     *     lazy: bool,
     *     disposable: bool
     * }
     */
    public function toArray() : array
    {
        return [
            'name' => $this->name,
            'storage' => $this->storage,
            'scopeKind' => $this->scopeKind(),
            'shared' => $this->isShared(),
            'scoped' => $this->isScoped(),
            'transient' => $this->isTransient(),
            'warm' => $this->warm,
            'lazy' => $this->lazy,
            'disposable' => $this->disposable,
        ];
    }

    private static function storageFor(string $name) : string
    {
        return match ($name) {
            SharedLifetime::NAME => SharedLifetime::NAME,
            ScopedLifetime::NAME,
            OperationLifetime::NAME,
            RequestLifetime::NAME,
            JobLifetime::NAME,
            TenantLifetime::NAME => ScopedLifetime::NAME,
            default => TransientLifetime::NAME,
        };
    }

    private static function scopeKindFor(string $name) : string
    {
        return match ($name) {
            OperationLifetime::NAME => ScopeKind::OPERATION,
            RequestLifetime::NAME => ScopeKind::REQUEST,
            JobLifetime::NAME => ScopeKind::JOB,
            TenantLifetime::NAME => ScopeKind::TENANT,
            ScopedLifetime::NAME => ScopeKind::ANY,
            default => '',
        };
    }
}
