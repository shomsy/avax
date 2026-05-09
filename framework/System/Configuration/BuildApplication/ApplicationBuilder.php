<?php

declare(strict_types=1);

namespace Avax\Framework\System\Configuration\BuildApplication;

use Avax\Framework\System\Capabilities\ComponentRegistry\ComponentProviderInterface;
use Avax\Framework\System\Capabilities\Configuration\RegisterConfigCommands;
use Avax\Framework\System\Capabilities\Doctor\RegisterDoctorCommands;
use Avax\Framework\System\Capabilities\Routing\RegisterRouteCommands;
use Avax\Framework\System\Capabilities\Runtime\RuntimeInterface;
use Avax\Framework\System\Capabilities\Runtime\RuntimeRequest;
use Avax\Framework\System\Capabilities\Runtime\RuntimeResponse;
use Avax\Framework\System\Flows\HandleIncomingHttp\ConfiguredRoutesHttpHandler;
use Avax\Framework\System\Flows\RunDoctor\RunDoctor;
use Avax\Framework\System\Foundation\Environment\EnvironmentName;
use Avax\Framework\System\Foundation\Paths\ProjectPath;
use Avax\Framework\System\Foundation\Time\Clock;
use Avax\Framework\System\Foundation\Time\SystemClock;
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

    private ?Closure $httpHandler = null;

    public function __construct(
        private ProjectPath $projectPath,
        private EnvironmentName $environmentName,
        private Clock $clock = new SystemClock(),
        private string $runtimeName = 'avax',
    ) {
        $this->registerConsoleCommand(
            name   : 'runtime:doctor',
            command: static function (array $args): string {
                $workerMode = in_array('--worker', $args, true);
                $runDoctor = new RunDoctor();
                $exitCode = $runDoctor->handle(workerMode: $workerMode);

                return $exitCode === 0 ? "Runtime doctor passed\n" : "Runtime doctor failed\n";
            },
        );

        $this->registerV4DxCommands();
    }

    /**
     * Register V4-04 developer experience commands (doctor, validate, inspect, config:*).
     */
    private function registerV4DxCommands(): void
    {
        $doctorCommands = (new RegisterDoctorCommands())();

        foreach ($doctorCommands as $name => $command) {
            $this->registerConsoleCommand(name: $name, command: $command);
        }

        $configCommands = (new RegisterConfigCommands())();

        foreach ($configCommands as $name => $command) {
            $this->registerConsoleCommand(name: $name, command: $command);
        }

        $routeCommands = (new RegisterRouteCommands())();

        foreach ($routeCommands as $name => $command) {
            $this->registerConsoleCommand(name: $name, command: $command);
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
            : new EnvironmentName(value: $environment);

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
     * @param  callable(RuntimeRequest, RuntimeInterface):RuntimeResponse|ConfiguredRoutesHttpHandler  $httpHandler
     */
    public function withHttpHandler(callable|ConfiguredRoutesHttpHandler $httpHandler): self
    {
        $clone = clone $this;
        $clone->httpHandler = $httpHandler instanceof ConfiguredRoutesHttpHandler
            ? $httpHandler->__invoke(...)
            : Closure::fromCallable($httpHandler);

        return $clone;
    }

    public function withHttpRouteDefinitions(callable $routeDefinitions): self
    {
        return $this->withHttpHandler(
            httpHandler: ConfiguredRoutesHttpHandler::fromRouteDefinitions(
                routeDefinitions: $routeDefinitions,
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
            httpHandler: ConfiguredRoutesHttpHandler::fromRoutesFile(
                routesFile: $resolvedPath,
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

    public function httpHandler(): ?Closure
    {
        return $this->httpHandler;
    }
}
