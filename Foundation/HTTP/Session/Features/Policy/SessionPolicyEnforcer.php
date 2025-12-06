<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Features\Policy;

use Avax\HTTP\Session\Core\SessionContext;
use Avax\HTTP\Session\Exceptions\SessionException;

final class SessionPolicyEnforcer
{
    /** @var array<SessionPolicyInterface> */
    private array $policies = [];

    public function addPolicy(SessionPolicyInterface $policy): self
    {
        $this->policies[] = $policy;
        return $this;
    }

    public function enforce(SessionContext $context): void
    {
        foreach ($this->policies as $policy) {
            if (!$policy->validate($context)) {
                throw new SessionException($policy->getMessage());
            }
        }
    }
}
