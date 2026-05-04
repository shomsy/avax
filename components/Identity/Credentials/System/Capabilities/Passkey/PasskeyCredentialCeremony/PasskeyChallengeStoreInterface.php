<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Capabilities\Passkey\PasskeyCredentialCeremony;

use DateTimeImmutable;

interface PasskeyChallengeStoreInterface
{
    public function issue(PasskeyChallengeRecord $passkeyChallengeRecord) : void;

    public function find(string $challengeId): ?PasskeyChallengeRecord;

    public function markUsed(string $challengeId, DateTimeImmutable $usedAt): void;

    public function forget(string $challengeId): void;
}
