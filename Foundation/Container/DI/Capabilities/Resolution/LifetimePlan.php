<?php

declare(strict_types=1);

namespace Avax\Container\DI\Capabilities\Resolution;

use Avax\Container\DI\Capabilities\Declaration\Bindings\ServiceRegistration;
use Avax\Container\DI\Capabilities\Runtime\Scopes\Lifetimes\JobLifetime;
use Avax\Container\DI\Capabilities\Runtime\Scopes\Lifetimes\OperationLifetime;
use Avax\Container\DI\Capabilities\Runtime\Scopes\Lifetimes\PooledLifetime;
use Avax\Container\DI\Capabilities\Runtime\Scopes\Lifetimes\RequestLifetime;
use Avax\Container\DI\Capabilities\Runtime\Scopes\Lifetimes\ScopedLifetime;
use Avax\Container\DI\Capabilities\Runtime\Scopes\Lifetimes\SharedLifetime;
use Avax\Container\DI\Capabilities\Runtime\Scopes\Lifetimes\TenantLifetime;
use Avax\Container\DI\Capabilities\Runtime\Scopes\Lifetimes\TransientLifetime;
use Avax\Container\DI\Capabilities\Runtime\Scopes\ScopeKind;

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
        public bool   $warm = false,
        public bool   $lazy = false,
        public bool   $disposable = false,
        public int    $poolSize = 8,
        public bool   $poolResetBeforeReuse = true
    ) {}

    public static function fromRegistration(string $serviceId, ServiceRegistration|null $registration) : self
    {
        $name      = $registration?->lifetime ?? TransientLifetime::NAME;
        $storage   = self::storageFor(name: $name);
        $scopeKind = $name === PooledLifetime::NAME
            ? (string) ($registration?->poolScopeKind ?? ScopeKind::OPERATION)
            : self::scopeKindFor(name: $name);

        return new self(
            serviceId           : $serviceId,
            name                : $name,
            storage             : $storage,
            scopeKind           : $scopeKind,
            warm                : (bool) ($registration?->warm ?? false),
            lazy                : (bool) ($registration?->lazy ?? false),
            disposable          : (bool) ($registration?->disposable ?? false),
            poolSize            : max(1, (int) ($registration?->poolSize ?? 8)),
            poolResetBeforeReuse: (bool) ($registration?->poolResetBeforeReuse ?? true)
        );
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
            PooledLifetime::NAME => PooledLifetime::NAME,
            default              => TransientLifetime::NAME,
        };
    }

    private static function scopeKindFor(string $name) : string
    {
        return match ($name) {
            OperationLifetime::NAME => ScopeKind::OPERATION,
            RequestLifetime::NAME   => ScopeKind::REQUEST,
            JobLifetime::NAME       => ScopeKind::JOB,
            TenantLifetime::NAME    => ScopeKind::TENANT,
            PooledLifetime::NAME    => ScopeKind::OPERATION,
            ScopedLifetime::NAME    => ScopeKind::ANY,
            default                 => '',
        };
    }

    /**
     * @param array<string, mixed> $state
     */
    public static function fromArray(string $serviceId, array $state) : self
    {
        $name      = (string) ($state['name'] ?? TransientLifetime::NAME);
        $scopeKind = (string) ($state['scopeKind'] ?? self::scopeKindFor(name: $name));

        return new self(
            serviceId           : $serviceId,
            name                : $name,
            storage             : (string) ($state['storage'] ?? self::storageFor(name: $name)),
            scopeKind           : $scopeKind,
            warm                : (bool) ($state['warm'] ?? false),
            lazy                : (bool) ($state['lazy'] ?? false),
            disposable          : (bool) ($state['disposable'] ?? false),
            poolSize            : max(1, (int) ($state['poolSize'] ?? 8)),
            poolResetBeforeReuse: (bool) ($state['poolResetBeforeReuse'] ?? true)
        );
    }

    public function requiresScope() : bool
    {
        return $this->isScoped() || $this->isPooled();
    }

    public function isScoped() : bool
    {
        return $this->storage === ScopedLifetime::NAME;
    }

    public function isPooled() : bool
    {
        return $this->storage === PooledLifetime::NAME;
    }

    /**
     * @return array{
     *     name: string,
     *     storage: string,
     *     scopeKind: string,
     *     shared: bool,
     *     scoped: bool,
     *     transient: bool,
     *     pooled: bool,
     *     warm: bool,
     *     lazy: bool,
     *     disposable: bool,
     *     poolSize: int,
     *     poolResetBeforeReuse: bool
     * }
     */
    public function toArray() : array
    {
        return [
            'name'                 => $this->name,
            'storage'              => $this->storage,
            'scopeKind'            => $this->scopeKind(),
            'shared'               => $this->isShared(),
            'scoped'               => $this->isScoped(),
            'transient'            => $this->isTransient(),
            'pooled'               => $this->isPooled(),
            'warm'                 => $this->warm,
            'lazy'                 => $this->lazy,
            'disposable'           => $this->disposable,
            'poolSize'             => $this->poolSize,
            'poolResetBeforeReuse' => $this->poolResetBeforeReuse,
        ];
    }

    public function scopeKind() : string
    {
        return $this->scopeKind !== '' ? $this->scopeKind : ScopeKind::ANY;
    }

    public function isShared() : bool
    {
        return $this->storage === SharedLifetime::NAME;
    }

    public function isTransient() : bool
    {
        return $this->storage === TransientLifetime::NAME;
    }
}
