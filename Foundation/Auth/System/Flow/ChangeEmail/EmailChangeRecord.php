<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\ChangeEmail;

use Avax\Auth\System\Capability\User\UserId;
use DateTimeImmutable;

final readonly class EmailChangeRecord
{
    public function __construct(
        public UserId                        $userId,
        #[\SensitiveParameter] public string $newEmail,
        public DateTimeImmutable             $expiresAt
    ) {}
}
