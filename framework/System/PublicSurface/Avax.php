<?php

declare(strict_types=1);

namespace Avax\Framework\System\PublicSurface;

use Avax\Framework\System\Flows\CreateApplication\CreateApplication;
use Avax\Framework\System\Configuration\BuildApplication\ApplicationBuilder;

/**
 * Avax — Framework entry point.
 *
 * V4 simple API:
 *   $app = Avax::create();
 *   $app->get('/', fn () => 'Hello AvaX');
 *   $app->run();
 *
 * Advanced API (existing):
 *   $avax = Avax::boot(ApplicationBuilder::fromProjectPath(...));
 */
final readonly class Avax implements AvaxInterface
{
    public function __construct(
        private App $app,
    ) {
    }

    /**
     * Zero-configuration app factory.
     *
     * Creates a tiny runnable App API over existing AvaX internals.
     * No manual config required for the simplest app.
     */
    public static function create(string $environment = 'production'): App
    {
        return (new CreateApplication())->make(environment: $environment);
    }

    /**
     * Full boot with ApplicationBuilder (existing advanced API).
     */
    public static function boot(ApplicationBuilder $builder): self
    {
        return self::bootInternal(builder: $builder);
    }

    /**
     * Delegate to the existing boot implementation.
     */
    private static function bootInternal(ApplicationBuilder $builder): self
    {
        $bootFlow = new \Avax\Framework\System\Flows\BootApplication\BootApplication(
            buildApplicationState: new \Avax\Framework\System\Flows\BootApplication\BuildApplicationState(),
        );
        $runtime = $bootFlow->boot(builder: $builder);

        return new self(
            app: new App(
                runtime: $runtime,
                resetApplicationState: new \Avax\Framework\System\Flows\ResetApplicationState\ResetApplicationState(
                    stateResetRegistry: $runtime->stateResetRegistry(),
                ),
            ),
        );
    }

    public function state(): \Avax\Framework\System\Capabilities\Runtime\RuntimeState
    {
        return $this->app->runtime()->state();
    }

    public function context(): \Avax\Framework\System\Capabilities\Runtime\RuntimeContext
    {
        return $this->app->runtime()->context();
    }

    public function requestScopes(): \Avax\Framework\System\Capabilities\RequestScope\RequestScopeStore
    {
        return $this->app->runtime()->requestScopes();
    }

    public function components(): \Avax\Framework\System\Capabilities\ComponentRegistry\ComponentRegistry
    {
        return $this->app->runtime()->components();
    }

    public function http(): \Avax\Framework\System\PublicSurface\Http\HttpKernelInterface
    {
        return $this->app->asHttpKernel();
    }

    public function console(): \Avax\Framework\System\PublicSurface\Console\ConsoleKernelInterface
    {
        return $this->app->asConsoleKernel();
    }

    public function runtime(): \Avax\Framework\System\PublicSurface\Runtime\RuntimeKernelInterface
    {
        return $this->app->asRuntimeKernel();
    }

    public function resetState(): \Avax\Framework\System\Capabilities\StateReset\StateResetReport
    {
        return $this->app->resetState();
    }
}
