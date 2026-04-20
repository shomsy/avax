<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\ChangeEmail;

use Avax\Auth\System\Capabilities\User\UserId;
use DateTimeImmutable;

interface EmailChangeStoreInterface
{
    public function issue(UserId $userId, string $newEmail, DateTimeImmutable $expiresAt) : EmailChangeChallenge;

    public function consume(string $token, DateTimeImmutable $now) : EmailChangeRecord|null;
}
