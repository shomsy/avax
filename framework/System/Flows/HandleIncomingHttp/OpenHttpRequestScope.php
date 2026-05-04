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
        private RequestScopeStore $requestScopeStore,
        private RuntimeContext $runtimeContext,
    ) {}

    /**
     * @throws RandomException
     */
    public function open(RuntimeRequest $runtimeRequest) : RequestScope
    {
        $requestScope = $this->requestScopeStore->open();
        $this->runtimeContext->startRequest(
            requestScopeId: $requestScope->id(),
            runtimeRequest: $runtimeRequest,
        );

        return $requestScope;
    }
}
