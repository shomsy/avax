<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Passkey;

use DateTimeImmutable;

interface PasskeyChallengeStoreInterface
{
    public function issue(PasskeyChallengeRecord $record) : void;

    public function find(string $challengeId) : PasskeyChallengeRecord|null;

    public function markUsed(string $challengeId, DateTimeImmutable $usedAt) : void;

    public function forget(string $challengeId) : void;
}
