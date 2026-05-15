<?php

declare(strict_types=1);

namespace Avax\Framework\System\PublicSurface;

use Avax\Components\HTTP\Request\System\Flows\CreateRequestFromGlobals\CreateRequestFromGlobals;
use Avax\Components\HTTP\Request\System\Flows\CreateRequestFromGlobals\ReadQueryParameters;
use Avax\Components\HTTP\Request\System\Flows\CreateRequestFromGlobals\ReadRequestBody;
use Avax\Components\HTTP\Request\System\Flows\CreateRequestFromGlobals\ReadServerParameters;
use Avax\Components\HTTP\Request\System\Flows\CreateRequestFromGlobals\ReadUploadedFiles;
use Avax\Components\HTTP\Request\System\Capabilities\Body\ParseFormBody;
use Avax\Components\HTTP\Request\System\Capabilities\Body\ParseJsonBody;
use Avax\Components\HTTP\Request\System\Capabilities\Files\NormalizeUploadedFiles;
use Avax\Components\HTTP\Request\System\Capabilities\Headers\NormalizeHeaders;
use Avax\Components\HTTP\Response\System\Capabilities\CreateHttpResponse\CreateHttpResponse;
use Avax\Framework\System\Capabilities\ComponentRegistry\ComponentRegistry;
use Avax\Framework\System\Capabilities\PreCommit\Configuration\PreCommitConfig;
use Avax\Framework\System\Capabilities\PreCommit\PreCommit;
use Avax\Framework\System\Capabilities\RequestScope\RequestScopeStore;
use Avax\Framework\System\Capabilities\Runtime\RuntimeContext;
use Avax\Framework\System\Capabilities\Runtime\RuntimeInterface;
use Avax\Framework\System\Capabilities\Runtime\RuntimeState;
use Avax\Framework\System\Capabilities\StateReset\StateResetRegistry;
use Avax\Framework\System\Capabilities\StateReset\StateResetReport;
use Avax\Framework\System\Configuration\BuildApplication\Builders\ApplicationBuilder;
use Avax\Framework\System\Flows\BootApplication\BootApplication;
use Avax\Framework\System\Flows\BootApplication\BuildApplicationState;
use Avax\Framework\System\Flows\CreateApplication\CreateApplication;
use Avax\Framework\System\Flows\HandleIncomingHttp\HandleIncomingHttp;
use Avax\Framework\System\Flows\ResetApplicationState\ResetApplicationState;
use Avax\Framework\System\Flows\RunConsoleCommand\RunConsoleCommand;
use Avax\Framework\System\Foundation\Paths\ProjectPath;
use Avax\Framework\System\Foundation\Time\SystemClock;
use Avax\Framework\System\PublicSurface\Console\ConsoleKernel;
use Avax\Framework\System\PublicSurface\Console\ConsoleKernelInterface;
use Avax\Framework\System\PublicSurface\Http\HttpKernel;
use Avax\Framework\System\PublicSurface\Http\HttpKernelInterface;
use Avax\Framework\System\PublicSurface\Runtime\RuntimeKernel;
use Avax\Framework\System\PublicSurface\Runtime\RuntimeKernelInterface;

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
        private RuntimeInterface $runtime,
        private HttpKernelInterface $httpKernel,
        private ConsoleKernelInterface $consoleKernel,
        private RuntimeKernelInterface $runtimeKernel,
        private ResetApplicationState $resetApplicationState,
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
        $createHttpResponse = new CreateHttpResponse();

        $createRequestFromGlobals = new CreateRequestFromGlobals(
            readServerParameters  : new ReadServerParameters(),
            readQueryParameters   : new ReadQueryParameters(),
            readUploadedFiles     : new ReadUploadedFiles(),
            readRequestBody       : new ReadRequestBody(),
            normalizeHeaders      : new NormalizeHeaders(),
            normalizeUploadedFiles: new NormalizeUploadedFiles(),
            parseJsonBody         : new ParseJsonBody(),
            parseFormBody         : new ParseFormBody(),
        );

        return (new CreateApplication(
            clock: new SystemClock(),
            projectPath: new ProjectPath(value: getcwd() ?: __DIR__ . '/../../..'),
            componentRegistry: new ComponentRegistry(),
            requestScopeStore: new RequestScopeStore(),
            runtimeContext: new RuntimeContext(),
            stateResetRegistry: new StateResetRegistry(),
            createHttpResponse: $createHttpResponse,
            handleIncomingHttp: new HandleIncomingHttp(createHttpResponse: $createHttpResponse),
            createRequestFromGlobals: $createRequestFromGlobals,
        ))->make(environment: $environment);
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
        $createHttpResponse = new CreateHttpResponse();
        $handleIncomingHttp = new HandleIncomingHttp(createHttpResponse: $createHttpResponse);
        $bootFlow = new BootApplication(
            buildApplicationState: new BuildApplicationState(
                componentRegistry : new ComponentRegistry(),
                requestScopeStore : new RequestScopeStore(),
                runtimeContext    : new RuntimeContext(),
                stateResetRegistry: new StateResetRegistry(),
            ),
        );
        $runtime = $bootFlow->boot(builder: $builder);

        return new self(
            runtime              : $runtime,
            httpKernel           : new HttpKernel(
                runtime: $runtime,
                handleIncomingHttp: $handleIncomingHttp,
            ),
            consoleKernel        : new ConsoleKernel(
                runConsoleCommand: new RunConsoleCommand(
                    runtime: $runtime,
                    preCommitConfig: new PreCommitConfig(),
                    preCommit: new PreCommit(
                        preCommitConfig: new PreCommitConfig(),
                    ),
                ),
            ),
            runtimeKernel        : new RuntimeKernel(runtime: $runtime),
            resetApplicationState: new ResetApplicationState(
                stateResetRegistry: $runtime->stateResetRegistry(),
            ),
        );
    }

    public function state(): RuntimeState
    {
        return $this->runtime->state();
    }

    public function context(): RuntimeContext
    {
        return $this->runtime->context();
    }

    public function requestScopes(): RequestScopeStore
    {
        return $this->runtime->requestScopes();
    }

    public function components(): ComponentRegistry
    {
        return $this->runtime->components();
    }

    public function http(): HttpKernelInterface
    {
        return $this->httpKernel;
    }

    public function console(): ConsoleKernelInterface
    {
        return $this->consoleKernel;
    }

    public function runtime(): RuntimeKernelInterface
    {
        return $this->runtimeKernel;
    }

    public function resetState(): StateResetReport
    {
        return $this->resetApplicationState->reset();
    }
}
