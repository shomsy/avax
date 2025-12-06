<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Features\Policy\Policies;

use Avax\HTTP\Session\Core\SessionContext;
use Avax\HTTP\Session\Features\Policy\SessionPolicyInterface;

final readonly class MaxLifetimePolicy implements SessionPolicyInterface
{
    public function __construct(private int $maxLifetimeSeconds) {}

    public function validate(SessionContext $context): bool
    {
        $lifetime = time() - ($context->custom['created_at'] ?? time());
        return $lifetime <= $this->maxLifetimeSeconds;
    }

    public function getMessage(): string
    {
        return "Session lifetime exceeded {$this->maxLifetimeSeconds} seconds";
    }
}
