<?php

declare(strict_types=1);

namespace Avax\Framework\System\Configuration\BootDsl;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\HTTP\Response\System\Capabilities\CreateHttpResponse\CreateHttpResponse;
use Avax\Framework\System\Configuration\FrameworkServiceProvider;
use Avax\Framework\System\Flows\HandleIncomingHttp\HandleIncomingHttp;
use Avax\Framework\System\Foundation\Environment\EnvironmentName;
use Avax\Framework\System\Foundation\Paths\ProjectPath;
use Avax\Framework\System\Foundation\Time\Clock;
use Avax\Framework\System\Foundation\Time\SystemClock;
use Closure;

final readonly class BuildBootDslEngine
{
    /**
     * @param list<class-string<ServiceProvider>> $providers
     * @param array<string, Closure>              $consoleCommands
     */
    public static function fromBootOptions(
        string $projectPath,
        string $environmentName,
        Clock|null $clock,
        string $runtimeName,
        Closure|null $httpHandler,
        array $providers,
        array $consoleCommands,
    ): BootDslEngine {
        $createHttpResponse = new CreateHttpResponse();

        $providerRegistry = new ProviderRegistry();
        $providerRegistry->pinFrameworkProvider(FrameworkServiceProvider::class);
        $providerRegistry->registerUserProviders($providers);

        return new BootDslEngine(
            providerRegistry   : $providerRegistry,
            projectPath        : new ProjectPath(value: $projectPath),
            environmentName    : EnvironmentName::fromString($environmentName),
            clock              : $clock ?? new SystemClock(),
            runtimeName        : $runtimeName,
            handleIncomingHttp : new HandleIncomingHttp(createHttpResponse: $createHttpResponse),
            httpHandler        : $httpHandler,
            consoleCommands    : $consoleCommands,
        );
    }
}
