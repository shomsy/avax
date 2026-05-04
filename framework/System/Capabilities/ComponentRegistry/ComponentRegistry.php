<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\ComponentRegistry;

use Avax\Framework\System\Capabilities\Runtime\RuntimeInterface;
use Avax\Framework\System\Foundation\Failure\FrameworkMisconfigured;
use Closure;

final class ComponentRegistry
{
    /**
     * @var array<string, ComponentDefinition>
     */
    private array $definitions = [];

    /**
     * @var array<string, ComponentProviderInterface>
     */
    private array $providers = [];

    /**
     * @var array<string, mixed>
     */
    private array $instances = [];

    public function register(ComponentDefinition $definition): void
    {
        if ($this->has(name: $definition->name())) {
            throw new FrameworkMisconfigured(
                message: sprintf('Component "%s" is already registered.', $definition->name()),
            );
        }

        $this->definitions[$definition->name()] = $definition;
    }

    public function single(string $name, Closure $factory) : void
    {
        if (! isset($this->instances[$name])) {
            $this->instances[$name] = $factory($this);
        }
    }

    public function get(string $name) : mixed
    {
        if (! isset($this->instances[$name])) {
            throw new FrameworkMisconfigured(
                message: sprintf('Component "%s" not found in registry.', $name),
            );
        }

        return $this->instances[$name];
    }

    public function registerDefinition(string $name, ComponentDefinition $componentDefinition): void
    {
        $this->register(definition: $componentDefinition);
    }

    public function registerProvider(ComponentProviderInterface $provider): void
    {
        $this->registerProviderInstance(provider: $provider);
    }

    public function registerProviderInstance(ComponentProviderInterface $provider): void
    {
        $name = $provider::name();

        $this->register(
            definition: new ComponentDefinition(
                name: $name,
                providerClass: $provider::class,
            ),
        );

        $this->providers[$name] = $provider;
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->definitions);
    }

    /**
     * @return list<ComponentDefinition>
     */
    public function all(): array
    {
        return array_values($this->definitions);
    }

    /**
     * @return list<string>
     */
    public function names(): array
    {
        return array_keys($this->definitions);
    }

    public function boot(RuntimeInterface $runtime): void
    {
        $this->bootRuntime(runtime: $runtime);
    }

    public function bootRuntime(RuntimeInterface $runtime): void
    {
        foreach ($this->providers as $provider) {
            $provider->boot(runtime: $runtime);
        }
    }
}
