<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\ChangeEmail;

use Avax\Auth\System\Capability\User\UserId;
use DateTimeImmutable;

interface EmailChangeStoreInterface
{
    public function issue(UserId $userId, string $newEmail, DateTimeImmutable $expiresAt) : EmailChangeChallenge;

    public function consume(string $token, DateTimeImmutable $now) : EmailChangeRecord|null;
}
