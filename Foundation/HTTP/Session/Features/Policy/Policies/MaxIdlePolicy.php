<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Features\Policy\Policies;

use Avax\HTTP\Session\Core\SessionContext;
use Avax\HTTP\Session\Features\Policy\SessionPolicyInterface;

final readonly class MaxIdlePolicy implements SessionPolicyInterface
{
    public function __construct(private int $maxIdleSeconds) {}

    public function validate(SessionContext $context): bool
    {
        $idle = time() - ($context->custom['last_active_at'] ?? time());
        return $idle <= $this->maxIdleSeconds;
    }

    public function getMessage(): string
    {
        return "Session idle time exceeded {$this->maxIdleSeconds} seconds";
    }
}
