<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\HandleIncomingHttp;

use Avax\Framework\System\Capabilities\RequestScope\RequestScope;
use Avax\Framework\System\Capabilities\RequestScope\RequestScopeStore;
use Avax\Framework\System\Capabilities\Runtime\RuntimeContext;
use Avax\Framework\System\Capabilities\Runtime\RuntimeRequest;
use Random\RandomException;

final readonly class OpenHttpRequestScope
{
    public function __construct(
        private RequestScopeStore $requestScopes,
        private RuntimeContext $runtimeContext,
    ) {}

    /**
     * @throws RandomException
     */
    public function open(RuntimeRequest $request) : RequestScope
    {
        $requestScope = $this->requestScopes->open();
        $this->runtimeContext->startRequest(
            requestScopeId: $requestScope->id(),
            runtimeRequest: $request,
        );

        return $requestScope;
    }
}
