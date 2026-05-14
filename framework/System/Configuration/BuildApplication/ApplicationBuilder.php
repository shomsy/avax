<?php

declare(strict_types=1);

namespace Avax\Framework\System\Configuration\BuildApplication;

use Avax\Components\HTTP\System\Capabilities\ResponseBuilding\ResponseFactory;
use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use Avax\Framework\System\Capabilities\ComponentRegistry\ComponentProviderInterface;
use Avax\Framework\System\Capabilities\Configuration\RegisterConfigCommands;
use Avax\Framework\System\Capabilities\Doctor\RegisterDoctorCommands;
use Avax\Framework\System\Capabilities\Queue\RegisterQueueCommands;
use Avax\Framework\System\Capabilities\Routing\RegisterRouteCommands;
use Avax\Framework\System\Capabilities\Runtime\RuntimeInterface;
use Avax\Framework\System\Capabilities\Runtime\RuntimeRequest;
use Avax\Framework\System\Capabilities\Runtime\RuntimeResponse;
use Avax\Framework\System\Flows\HandleIncomingHttp\DispatchConfiguredRoute;
use Avax\Framework\System\Flows\HandleIncomingHttp\HandleIncomingHttp;
use Avax\Framework\System\Flows\RunDoctor\RunDoctor;
use Avax\Framework\System\Foundation\Environment\EnvironmentName;
use Avax\Framework\System\Foundation\Paths\ProjectPath;
use Avax\Framework\System\Foundation\Time\Clock;
use Closure;

final class ApplicationBuilder
{
    /**
     * @var list<ComponentProviderInterface>
     */
    private array $componentProviders = [];

    /**
     * @var array<string, Closure>
     */
    private array $consoleCommands = [];

    private Closure|null $httpHandler = null;

    public function __construct(
        private ProjectPath $projectPath,
        private EnvironmentName $environmentName,
        private Clock $clock,
        private RunDoctor $runDoctor,
        private HandleIncomingHttp $handleIncomingHttp,
        private Filesystem $filesystem,
        private ResponseFactory $responseFactory,
        private string $runtimeName = 'avax',
    ) {
        $doctor = $this->runDoctor;
        $this->registerConsoleCommandDirectly(
            name   : 'runtime:doctor',
            command: static function (array $args) use ($doctor): string {
                $workerMode = in_array('--worker', $args, true);
                $exitCode = $runDoctor->handle(workerMode: $workerMode);

                return $exitCode === 0 ? "Runtime doctor passed\n" : "Runtime doctor failed\n";
            },
        );

        $this->registerV4DxCommandsDirectly();
    }

    /**
     * Register a console command directly on this instance (constructor-only).
     */
    private function registerConsoleCommandDirectly(string $name, callable $command): void
    {
        $this->consoleCommands[$name] = Closure::fromCallable($command);
    }

    /**
     * Register V4-04 developer experience commands directly on this instance (constructor-only).
     */
    private function registerV4DxCommandsDirectly(): void
    {
        $doctorCommands = (new RegisterDoctorCommands())();

        foreach ($doctorCommands as $name => $command) {
            $this->registerConsoleCommandDirectly(name: $name, command: $command);
        }

        $configCommands = (new RegisterConfigCommands($this->filesystem))();

        foreach ($configCommands as $name => $command) {
            $this->registerConsoleCommandDirectly(name: $name, command: $command);
        }

        $routeCommands = (new RegisterRouteCommands($this->filesystem))();

        foreach ($routeCommands as $name => $command) {
            $this->registerConsoleCommandDirectly(name: $name, command: $command);
        }

        // Queue commands require runtime dependencies (broker, failed store, resolver, worker loop factory).
        // Skip during simple boot; queue CLI is registered when queue infrastructure is explicitly configured.
        if (class_exists(RegisterQueueCommands::class)) {
            // Deferred: queue commands need proper assembly via ServiceProvider or explicit configuration.
        }
    }

    public function projectPath(): ProjectPath
    {
        return $this->projectPath;
    }

    public function environment(): EnvironmentName
    {
        return $this->environmentName;
    }

    public function clock(): Clock
    {
        return $this->clock;
    }

    public function runtimeName(): string
    {
        return $this->runtimeName;
    }

    public function withEnvironment(EnvironmentName|string $environment): self
    {
        $clone = clone $this;
        $clone->environmentName = $environment instanceof EnvironmentName
            ? $environment
            : EnvironmentName::fromString($environment);

        return $clone;
    }

    public function withClock(Clock $clock): self
    {
        $clone = clone $this;
        $clone->clock = $clock;

        return $clone;
    }

    public function withRuntimeName(string $runtimeName): self
    {
        $clone = clone $this;
        $clone->runtimeName = trim(string: $runtimeName);

        return $clone;
    }

    /**
     * @param  callable(RuntimeRequest, RuntimeInterface):RuntimeResponse|DispatchConfiguredRoute  $httpHandler
     */
    public function withHttpHandler(callable|DispatchConfiguredRoute $httpHandler): self
    {
        $clone = clone $this;
        $clone->httpHandler = $httpHandler instanceof DispatchConfiguredRoute
            ? $httpHandler->__invoke(...)
            : Closure::fromCallable($httpHandler);

        return $clone;
    }

    public function withHttpRouteDefinitions(callable $routeDefinitions): self
    {
        return $this->withHttpHandler(
            httpHandler: DispatchConfiguredRoute::fromRouteDefinitions(
                routeDefinitions: $routeDefinitions,
                responseFactory : $this->responseFactory,
            ),
        );
    }

    public function withHttpRoutes(string $routesFile): self
    {
        $resolvedPath = $this->projectPath->join(relativePath: $routesFile);

        if (str_starts_with(haystack: $routesFile, needle: '/')) {
            $resolvedPath = $routesFile;
        }

        return $this->withHttpHandler(
            httpHandler: DispatchConfiguredRoute::fromRoutesFile(
                routesFile: $resolvedPath,
                responseFactory: $this->responseFactory,
            ),
        );
    }

    public function registerConsoleCommand(string $name, callable $command): self
    {
        $clone = clone $this;
        $clone->consoleCommands[$name] = Closure::fromCallable($command);

        return $clone;
    }

    public function registerComponentProvider(ComponentProviderInterface $componentProvider): self
    {
        $clone = clone $this;
        $clone->componentProviders = [...$clone->componentProviders, $componentProvider];

        return $clone;
    }

    /**
     * @return list<ComponentProviderInterface>
     */
    public function componentProviders(): array
    {
        return $this->componentProviders;
    }

    /**
     * @return array<string, Closure>
     */
    public function consoleCommands(): array
    {
        return $this->consoleCommands;
    }

    public function httpHandler() : Closure|null
    {
        return $this->httpHandler;
    }

    public function handleIncomingHttp(): HandleIncomingHttp
    {
        return $this->handleIncomingHttp;
    }
}
