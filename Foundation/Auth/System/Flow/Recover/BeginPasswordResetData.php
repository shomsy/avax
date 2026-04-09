<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Recover;

/**
 * Boundary input for starting password recovery.
 */
final readonly class BeginPasswordResetData
{
    public function __construct(
        public string      $email,
        public string|null $ipAddress = null,
        public string|null $userAgent = null
    ) {}
}
