<?php

declare(strict_types=1);

namespace Avax\Framework\System\Configuration\BootDsl;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;

/**
 * ProviderRegistry — collects and orders ServiceProviders for the Boot DSL.
 *
 * Providers are stored in declaration order.
 * FrameworkCore providers are pinned to load first.
 */
final class ProviderRegistry
{
    /**
     * @var list<class-string<ServiceProvider>>
     */
    private array $frameworkProviders = [];

    /**
     * @var list<class-string<ServiceProvider>>
     */
    private array $userProviders = [];

    /**
     * Pin a framework core provider to load first.
     *
     * @param class-string<ServiceProvider> $providerClass
     */
    public function pinFrameworkProvider(string $providerClass): void
    {
        $this->frameworkProviders[] = $providerClass;
    }

    /**
     * Register a user-provided provider in declaration order.
     *
     * @param class-string<ServiceProvider> $providerClass
     */
    public function registerUserProvider(string $providerClass): void
    {
        $this->userProviders[] = $providerClass;
    }

    /**
     * Register multiple user providers in declaration order.
     *
     * @param list<class-string<ServiceProvider>> $providerClasses
     */
    public function registerUserProviders(array $providerClasses): void
    {
        foreach ($providerClasses as $class) {
            $this->registerUserProvider($class);
        }
    }

    /**
     * Return all providers in boot order:
     * framework providers first, then user providers.
     *
     * @return list<class-string<ServiceProvider>>
     */
    public function orderedProviders(): array
    {
        return [...$this->frameworkProviders, ...$this->userProviders];
    }

    /**
     * True if no providers have been registered.
     */
    public function isEmpty(): bool
    {
        return $this->frameworkProviders === [] && $this->userProviders === [];
    }
}
