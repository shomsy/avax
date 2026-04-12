<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\AdminRealm;

use DateTimeImmutable;

final readonly class AdminElevation
{
    public function __construct(
        public string $bindingId,
        public DateTimeImmutable $expiresAt
    ) {}
}
