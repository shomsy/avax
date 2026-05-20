<?php

declare(strict_types=1);

namespace Avax\Framework\System\PublicSurface;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Framework\System\Configuration\BootDsl\BuildBootDslEngine;
use Avax\Framework\System\Foundation\Time\Clock;
use Closure;
use LogicException;

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
     * @var list<class-string<ServiceProvider>>
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
     * @param class-string<ServiceProvider> $providerClass
     */
    public function withProvider(string $providerClass): self
    {
        $this->providers[] = $providerClass;

        return $this;
    }

    /**
     * Register multiple ServiceProviders.
     *
     * @param list<class-string<ServiceProvider>> $providerClasses
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
     * @throws LogicException if projectPath is not set.
     */
    public function create(): App
    {
        $projectPath = $this->projectPath;
        if ($projectPath === null) {
            throw new LogicException('Project path is required. Call ->from() before ->create().');
        }

        $engine = BuildBootDslEngine::fromBootOptions(
            projectPath     : $projectPath,
            environmentName : $this->environmentName,
            clock           : $this->clock,
            runtimeName     : $this->runtimeName,
            httpHandler     : $this->httpHandler,
            providers       : $this->providers,
            consoleCommands : $this->consoleCommands,
        );

        return $engine->boot();
    }
}
