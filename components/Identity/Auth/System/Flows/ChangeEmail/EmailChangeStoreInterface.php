<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Flows\ChangeEmail;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use DateTimeImmutable;

interface EmailChangeStoreInterface
{
    public function issue(UserId $userId, string $newEmail, DateTimeImmutable $expiresAt) : EmailChangeChallenge;

    public function consume(string $token, DateTimeImmutable $now) : ?EmailChangeRecord;
}
