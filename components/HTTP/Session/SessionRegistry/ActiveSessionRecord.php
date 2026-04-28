<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\SessionRegistry;

use SensitiveParameter;

final class ActiveSessionRecord
{
    public function __construct(
        #[SensitiveParameter] public readonly string $sessionId,
        public readonly int                          $registeredAt,
        public readonly int                          $lastActivity,
        public readonly array                        $metadata = []
    ) {}
}