<?php

declare(strict_types=1);

namespace Avax\Framework\System\PublicSurface;

use Avax\Framework\System\Configuration\BootDsl\BootDslEngine;
use Avax\Framework\System\Configuration\BootDsl\ProviderRegistry;
use Avax\Framework\System\Foundation\Environment\EnvironmentName;
use Avax\Framework\System\Foundation\Paths\ProjectPath;
use Avax\Framework\System\Foundation\Time\Clock;
use Avax\Framework\System\Foundation\Time\SystemClock;
use Closure;

/**
 * BootDsl — public-facing fluent DSL for application boot.
 *
 * This is the stable public entrypoint returned by Avax::dsl().
 * It delegates to internal Configuration/BootDsl machinery.
 *
 * Usage:
 *   $app = Avax::dsl()
 *       ->from(__DIR__)
 *       ->withProvider(AppServiceProvider::class)
 *       ->create();
 */
final class BootDsl
{
    private ?string $projectPath = null;
    private string $environmentName = 'production';
    private ?Clock $clock = null;
    private string $runtimeName = 'avax';
    private ?Closure $httpHandler = null;

    /**
     * @var list<class-string<\Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider>>
     */
    private array $providers = [];

    /**
     * @var array<string, Closure>
     */
    private array $consoleCommands = [];

    private function __construct()
    {
    }

    /**
     * Create a new BootDsl instance.
     */
    public static function make(): self
    {
        return new self();
    }

    /**
     * Set the project path and environment name.
     */
    public function from(string $projectPath, string $environmentName = 'production'): self
    {
        $this->projectPath = $projectPath;
        $this->environmentName = $environmentName;

        return $this;
    }

    /**
     * Override the default clock.
     */
    public function withClock(Clock $clock): self
    {
        $this->clock = $clock;

        return $this;
    }

    /**
     * Set a custom runtime name.
     */
    public function withRuntimeName(string $runtimeName): self
    {
        $this->runtimeName = $runtimeName;

        return $this;
    }

    /**
     * Register a single ServiceProvider.
     *
     * @param class-string<\Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider> $providerClass
     */
    public function withProvider(string $providerClass): self
    {
        $this->providers[] = $providerClass;

        return $this;
    }

    /**
     * Register multiple ServiceProviders.
     *
     * @param list<class-string<\Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider>> $providerClasses
     */
    public function withProviders(array $providerClasses): self
    {
        foreach ($providerClasses as $class) {
            $this->withProvider($class);
        }

        return $this;
    }

    /**
     * Register a console command.
     */
    public function withCommand(string $name, Closure $handler): self
    {
        $this->consoleCommands[$name] = $handler;

        return $this;
    }

    /**
     * Set a custom HTTP handler.
     */
    public function withHttpHandler(Closure $handler): self
    {
        $this->httpHandler = $handler;

        return $this;
    }

    /**
     * Execute the full boot lifecycle and return a ready App.
     *
     * @throws \LogicException if projectPath is not set.
     */
    public function create(): App
    {
        if ($this->projectPath === null) {
            throw new \LogicException('Project path is required. Call ->from() before ->create().');
        }

        $clock = $this->clock ?? new SystemClock();
        $projectPath = new ProjectPath(value: $this->projectPath);
        $environmentName = EnvironmentName::fromString($this->environmentName);

        // Build the HandleIncomingHttp service.
        $createHttpResponse = new \Avax\Components\HTTP\Response\System\Capabilities\CreateHttpResponse\CreateHttpResponse();
        $handleIncomingHttp = new \Avax\Framework\System\Flows\HandleIncomingHttp\HandleIncomingHttp(createHttpResponse: $createHttpResponse);

        // Build the provider registry.
        $providerRegistry = new ProviderRegistry();
        $providerRegistry->pinFrameworkProvider(\Avax\Framework\System\Configuration\FrameworkServiceProvider::class);
        $providerRegistry->registerUserProviders($this->providers);

        // Execute the boot engine.
        $engine = new BootDslEngine(
            providerRegistry   : $providerRegistry,
            projectPath        : $projectPath,
            environmentName    : $environmentName,
            clock              : $clock,
            runtimeName        : $this->runtimeName,
            handleIncomingHttp : $handleIncomingHttp,
            httpHandler        : $this->httpHandler,
            consoleCommands    : $this->consoleCommands,
        );

        return $engine->boot();
    }
}
