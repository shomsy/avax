<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\System\Flows\ChangeEmail;

use Avax\Components\Identity\Auth\System\System\Capabilities\Identity\User\UserId;
use DateTimeImmutable;
use SensitiveParameter;

final readonly class EmailChangeRecord
{
    public function __construct(
        public UserId            $userId,
        #[SensitiveParameter]
        public string            $newEmail,
        public DateTimeImmutable $expiresAt,
    ) {}
}
