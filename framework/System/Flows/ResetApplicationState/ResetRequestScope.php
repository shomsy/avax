<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\ResetApplicationState;

use Avax\Framework\System\Capabilities\RequestScope\RequestScope;

final class ResetRequestScope
{
    public function __construct(
        private readonly RequestScope $requestScope,
    ) {
    }

    public function reset() : void
    {
        $this->requestScope->clear();
    }
}
