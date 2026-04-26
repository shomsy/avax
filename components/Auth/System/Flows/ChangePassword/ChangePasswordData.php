<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\ChangePassword;

use SensitiveParameter;

/**
 * Value object representing password change data.
 *
 * Banal: The data needed to change a password.
 */
final readonly class ChangePasswordData
{
    public function __construct(
        #[SensitiveParameter] public string $currentPassword,
        #[SensitiveParameter] public string $newPassword
    ) {}
}
