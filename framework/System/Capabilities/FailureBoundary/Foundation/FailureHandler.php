<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\Foundation;

use Throwable;

interface FailureHandler
{
    public function __invoke(Throwable $failure, FailureContext $context): mixed;
}
