<?php

declare(strict_types=1);

namespace components\Container\DI\Capabilities\Resolution;

use components\Container\DI\Capabilities\Declaration\Bindings\ServiceRegistration;
use components\Container\DI\Capabilities\Runtime\Scopes\Lifetimes\JobLifetime;
use components\Container\DI\Capabilities\Runtime\Scopes\Lifetimes\OperationLifetime;
use components\Container\DI\Capabilities\Runtime\Scopes\Lifetimes\PooledLifetime;
use components\Container\DI\Capabilities\Runtime\Scopes\Lifetimes\RequestLifetime;
use components\Container\DI\Capabilities\Runtime\Scopes\Lifetimes\ScopedLifetime;
use components\Container\DI\Capabilities\Runtime\Scopes\Lifetimes\SharedLifetime;
use components\Container\DI\Capabilities\Runtime\Scopes\Lifetimes\TenantLifetime;
use components\Container\DI\Capabilities\Runtime\Scopes\Lifetimes\TransientLifetime;
use components\Container\DI\Capabilities\Runtime\Scopes\ScopeKind;

/**
 * One explicit lifetime decision for a resolved service.
 */
final readonly class LifetimePlan
{
    public bool   $poolResetBeforeReuse;
    public int    $poolSize;
    public bool   $disposable;
    public bool   $lazy;
    public bool   $warm;
    public string $scopeKind;
    public string $storage;
    public string $name;
    public string $serviceId;

    public function __construct(
        string      $serviceId,
        string      $name,
        string      $storage,
        string|null $scopeKind = null,
        bool|null   $warm = null,
        bool|null   $lazy = null,
        bool|null   $disposable = null,
        int|null    $poolSize = null,
        bool        $poolResetBeforeReuse = true
    )
    {
        $scopeKind                  ??= '';
        $warm                       ??= false;
        $lazy                       ??= false;
        $disposable                 ??= false;
        $poolSize                   ??= 8;
        $this->serviceId            = $serviceId;
        $this->name                 = $name;
        $this->storage              = $storage;
        $this->scopeKind            = $scopeKind;
        $this->warm                 = $warm;
        $this->lazy                 = $lazy;
        $this->disposable           = $disposable;
        $this->poolSize             = $poolSize;
        $this->poolResetBeforeReuse = $poolResetBeforeReuse;
    }

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
            OperationLifetime::NAME, PooledLifetime::NAME => ScopeKind::OPERATION,
            RequestLifetime::NAME                         => ScopeKind::REQUEST,
            JobLifetime::NAME                             => ScopeKind::JOB,
            TenantLifetime::NAME                          => ScopeKind::TENANT,
            ScopedLifetime::NAME                          => ScopeKind::ANY,
            default                                       => '',
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
