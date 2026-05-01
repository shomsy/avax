<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\HandleWorkerRequest;

use Avax\Framework\System\Capabilities\RequestScope\RequestScope;

final readonly class CloseWorkerRequestScope
{
    public function __construct(
        private RequestScope $requestScope,
    ) {
    }

    public function close(): void
    {
        $this->requestScope->close();
    }
}
