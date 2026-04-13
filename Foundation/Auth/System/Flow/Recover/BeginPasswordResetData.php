<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Recover;

use SensitiveParameter;

/**
 * Boundary input for starting password recovery.
 */
final readonly class BeginPasswordResetData
{
    public function __construct(
        #[SensitiveParameter] public string      $email,
        #[SensitiveParameter] public string|null $ipAddress = null,
        public string|null                       $userAgent = null
    ) {}
}
