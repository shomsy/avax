<?php

declare(strict_types=1);

namespace Avax\Components\Framework\System\Flows\HandleWorkerRequest;

use Avax\Components\Framework\System\Capabilities\RequestScope\RequestScope;

final class CloseWorkerRequestScope
{
    public function __construct(
        private readonly RequestScope $requestScope,
    ) {
    }

    public function close(): void
    {
        $this->requestScope->close();
    }
}