<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\SessionRegistry;

final class ActiveSessionRecord
{
    public function __construct(
        public readonly string $sessionId,
        public readonly int    $registeredAt,
        public readonly int    $lastActivity,
        public readonly array  $metadata = []
    ) {}
}