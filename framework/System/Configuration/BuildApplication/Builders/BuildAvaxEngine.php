<?php

declare(strict_types=1);

namespace Avax\Framework\System\Configuration\BuildApplication\Builders;

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
use Avax\Framework\System\Capabilities\StateReset\StateResetRegistry;
use Avax\Framework\System\Flows\BootApplication\BootApplication;
use Avax\Framework\System\Flows\BootApplication\BuildApplicationState;
use Avax\Framework\System\Flows\CreateApplication\CreateApplication;
use Avax\Framework\System\Flows\HandleIncomingHttp\HandleIncomingHttp;
use Avax\Framework\System\Flows\ResetApplicationState\ResetApplicationState;
use Avax\Framework\System\Flows\RunConsoleCommand\RunConsoleCommand;
use Avax\Framework\System\Foundation\Paths\ProjectPath;
use Avax\Framework\System\Foundation\Time\SystemClock;
use Avax\Framework\System\PublicSurface\App;
use Avax\Framework\System\PublicSurface\Avax;
use Avax\Framework\System\PublicSurface\Console\ConsoleKernel;
use Avax\Framework\System\PublicSurface\Http\HttpKernel;
use Avax\Framework\System\PublicSurface\Runtime\RuntimeKernel;

final readonly class BuildAvaxEngine
{
    /**
     * Zero-configuration app factory.
     *
     * Creates a tiny runnable App API over existing AvaX internals.
     */
    public static function createApp(string $environment): App
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
     * Full boot sequence delegator.
     */
    public static function boot(ApplicationBuilder $builder): Avax
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

        return new Avax(
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
}
