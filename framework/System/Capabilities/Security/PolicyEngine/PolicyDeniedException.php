<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Security\PolicyEngine;

use Avax\Framework\System\Capabilities\Security\PolicyEngine\Foundation\PolicyFailure;
use Exception;

final class PolicyDeniedException extends Exception
{
    public function __construct(
        public readonly PolicyFailure $failure,
    ) {
        parent::__construct($failure->message(), 403);
    }
}
