<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Flows\RecoverAccess\PasswordReset;

use SensitiveParameter;

/**
 * Boundary input for completing password recovery.
 */
final readonly class ResetPasswordData
{
    public function __construct(
        #[SensitiveParameter]
        public string $token,
        #[SensitiveParameter]
        public string $newPassword,
        #[SensitiveParameter]
        public ?string $ipAddress = null,
        public ?string $userAgent = null,
    ) {}
}
