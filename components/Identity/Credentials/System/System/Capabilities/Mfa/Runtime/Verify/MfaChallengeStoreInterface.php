<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\System\Capabilities\Mfa\Runtime\Verify;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;

/**
 * Stores MFA challenge lifecycle state.
 */
interface MfaChallengeStoreInterface
{
    public function issue(MfaChallengeRecord $mfaChallengeRecord) : void;

    public function find(string $challengeId) : ?MfaChallengeRecord;

    public function save(MfaChallengeRecord $mfaChallengeRecord) : void;

    public function forget(string $challengeId) : void;

    public function forgetForUser(UserId $userId) : void;
}
