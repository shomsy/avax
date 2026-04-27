<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\HandleIncomingHttp;

use Avax\Framework\System\Capabilities\RequestScope\RequestScope;
use Avax\Framework\System\Capabilities\RequestScope\RequestScopeStore;
use Avax\Framework\System\Capabilities\Runtime\RuntimeContext;
use Avax\Framework\System\Capabilities\Runtime\RuntimeRequest;

final readonly class OpenHttpRequestScope
{
    public function __construct(
        private RequestScopeStore $requestScopes,
        private RuntimeContext $runtimeContext,
    ) {
    }

    /**
     * @throws \Random\RandomException
     */
    public function open(RuntimeRequest $request): RequestScope
    {
        $scope = $this->requestScopes->open();
        $this->runtimeContext->startRequest(scopeId: $scope->id(), request: $request);

        return $scope;
    }
}
