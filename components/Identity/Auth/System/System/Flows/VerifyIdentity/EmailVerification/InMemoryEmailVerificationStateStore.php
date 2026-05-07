<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\System\Flows\VerifyIdentity\EmailVerification;

use Avax\Components\Identity\Auth\System\System\Capabilities\Identity\User\UserId;

/**
 * In-memory verification state for tests and demos.
 */
final class InMemoryEmailVerificationStateStore implements EmailVerificationStateStoreInterface
{
    /** @var array<int, true> */
    private array $verified = [];

    public function isVerified(UserId $userId) : bool
    {
        return isset($this->verified[$userId->value]);
    }

    public function markVerified(UserId $userId) : void
    {
        $this->verified[$userId->value] = true;
    }
}
