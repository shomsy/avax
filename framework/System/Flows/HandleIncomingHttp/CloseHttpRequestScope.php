<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\HandleIncomingHttp;

use Avax\Framework\System\Capabilities\RequestScope\RequestScopeStore;

final readonly class CloseHttpRequestScope
{
    public function __construct(private RequestScopeStore $requestScopes) {}

    public function close() : void
    {
        if ($this->requestScopes->hasCurrent()) {
            $this->requestScopes->closeCurrent();
        }
    }
}
