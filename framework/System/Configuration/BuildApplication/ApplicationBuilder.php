<?php

declare(strict_types=1);

namespace Avax\Framework\System\Configuration\BuildApplication;

use Avax\Framework\System\Capabilities\ComponentRegistry\ComponentProviderInterface;
use Avax\Framework\System\Flows\HandleIncomingHttp\ConfiguredRoutesHttpHandler;
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

    private Closure|null $httpHandler = null;

    public function __construct(
        private ProjectPath $projectPath,
        private EnvironmentName $environment,
        private Clock $clock = new SystemClock(),
        private string $runtimeName = 'avax',
    ) {
    }

    public function projectPath() : ProjectPath
    {
        return $this->projectPath;
    }

    public function environment() : EnvironmentName
    {
        return $this->environment;
    }

    public function clock() : Clock
    {
        return $this->clock;
    }

    public function runtimeName() : string
    {
        return $this->runtimeName;
    }

    public function withEnvironment(EnvironmentName|string $environment) : self
    {
        $clone              = clone $this;
        $clone->environment = $environment instanceof EnvironmentName
            ? $environment
            : new EnvironmentName(value: $environment);

        return $clone;
    }

    public function withClock(Clock $clock) : self
    {
        $clone        = clone $this;
        $clone->clock = $clock;

        return $clone;
    }

    public function withRuntimeName(string $runtimeName) : self
    {
        $clone              = clone $this;
        $clone->runtimeName = trim(string: $runtimeName);

        return $clone;
    }

    public function withHttpHandler(callable $httpHandler) : self
    {
        $clone              = clone $this;
        $clone->httpHandler = Closure::fromCallable($httpHandler);

        return $clone;
    }

    public function withHttpRouteDefinitions(callable $routeDefinitions) : self
    {
        return $this->withHttpHandler(
            httpHandler: ConfiguredRoutesHttpHandler::fromRouteDefinitions(
                routeDefinitions: $routeDefinitions,
            ),
        );
    }

    public function withHttpRoutes(string $routesFile) : self
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

    public function registerConsoleCommand(string $name, callable $command) : self
    {
        $clone                         = clone $this;
        $clone->consoleCommands[$name] = Closure::fromCallable($command);

        return $clone;
    }

    public function registerComponentProvider(ComponentProviderInterface $provider) : self
    {
        $clone                     = clone $this;
        $clone->componentProviders = [...$clone->componentProviders, $provider];

        return $clone;
    }

    /**
     * @return list<ComponentProviderInterface>
     */
    public function componentProviders() : array
    {
        return $this->componentProviders;
    }

    /**
     * @return array<string, Closure>
     */
    public function consoleCommands() : array
    {
        return $this->consoleCommands;
    }

    public function httpHandler() : Closure|null
    {
        return $this->httpHandler;
    }
}
