<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Flows\ChangeEmail;

use DateTimeImmutable;
use SensitiveParameter;

final readonly class EmailChangeChallenge
{
    public function __construct(
        public bool               $dispatched,
        #[SensitiveParameter]
        public ?string            $token = null,
        public ?DateTimeImmutable $expiresAt = null,
    ) {}
}
