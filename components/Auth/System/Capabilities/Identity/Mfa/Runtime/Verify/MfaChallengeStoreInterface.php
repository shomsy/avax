<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Verify;

use Avax\Auth\System\Capabilities\Identity\User\UserId;

/**
 * Stores MFA challenge lifecycle state.
 */
interface MfaChallengeStoreInterface
{
    public function issue(MfaChallengeRecord $record) : void;

    public function find(string $challengeId) : MfaChallengeRecord|null;

    public function save(MfaChallengeRecord $record) : void;

    public function forget(string $challengeId) : void;

    public function forgetForUser(UserId $userId) : void;
}
