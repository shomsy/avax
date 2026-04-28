<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Flows\ChangeEmail;

use DateTimeImmutable;
use SensitiveParameter;

final readonly class EmailChangeChallenge
{
    public function __construct(
        public bool                              $dispatched,
        #[SensitiveParameter] public string|null $token = null,
        public DateTimeImmutable|null            $expiresAt = null
    ) {}
}
