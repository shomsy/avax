<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\ChangeEmail;

use Avax\Auth\System\Capabilities\Identity\User\UserId;
use DateTimeImmutable;
use SensitiveParameter;

final readonly class EmailChangeRecord
{
    public function __construct(
        public UserId                       $userId,
        #[SensitiveParameter] public string $newEmail,
        public DateTimeImmutable            $expiresAt
    )
    {
    }
}
