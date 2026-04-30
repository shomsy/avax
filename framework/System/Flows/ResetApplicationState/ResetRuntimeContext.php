<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\ResetApplicationState;

use Avax\Framework\System\Capabilities\Runtime\RuntimeContext;

final class ResetRuntimeContext
{
    public function __construct(
        private readonly RuntimeContext $context,
    ) {
    }

    public function reset(): void
    {
        $this->context->clear();
    }
}
