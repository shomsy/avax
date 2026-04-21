<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\ChangeEmail;

use Avax\Auth\System\Capabilities\Identity\User\UserId;
use DateTimeImmutable;
use SensitiveParameter;

final readonly class EmailChangeRecord
{
    public DateTimeImmutable $expiresAt;
    public string            $newEmail;
    public UserId            $userId;

    public function __construct(
        UserId                       $userId,
        #[SensitiveParameter] string $newEmail,
        DateTimeImmutable            $expiresAt
    )
    {
        $this->userId    = $userId;
        $this->newEmail  = $newEmail;
        $this->expiresAt = $expiresAt;
    }
}
