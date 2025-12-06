<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Features\Policy\Policies;

use Avax\HTTP\Session\Core\SessionContext;
use Avax\HTTP\Session\Features\Policy\SessionPolicyInterface;

final readonly class SecureOnlyPolicy implements SessionPolicyInterface
{
    public function validate(SessionContext $context): bool
    {
        return $context->secure === true;
    }

    public function getMessage(): string
    {
        return "Session must use secure (encrypted) transport";
    }
}
