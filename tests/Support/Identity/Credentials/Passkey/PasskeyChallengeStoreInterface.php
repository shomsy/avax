<?php

declare(strict_types=1);

namespace Avax\Tests\Support\Identity\Credentials\Passkey;

use DateTimeImmutable;

interface PasskeyChallengeStoreInterface
{
    public function issue(PasskeyChallengeRecord $passkeyChallengeRecord): void;

    public function find(string $challengeId) : PasskeyChallengeRecord|null;

    public function markUsed(string $challengeId, DateTimeImmutable $usedAt): void;

    public function forget(string $challengeId): void;
}
