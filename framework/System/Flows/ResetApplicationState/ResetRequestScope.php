<?php

declare(strict_types=1);

namespace Avax\Components\Framework\System\Flows\ResetApplicationState;

use Avax\Components\Framework\System\Capabilities\RequestScope\RequestScope;

final class ResetRequestScope
{
    public function __construct(
        private readonly RequestScope $requestScope,
    ) {
    }

    public function reset(): void
    {
        $this->requestScope->clear();
    }
}