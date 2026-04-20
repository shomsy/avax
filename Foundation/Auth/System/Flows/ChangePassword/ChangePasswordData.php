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
    public string $newPassword;
    public string $currentPassword;

    public function __construct(
        #[SensitiveParameter] string $currentPassword,
        #[SensitiveParameter] string $newPassword
    )
    {
        $this->currentPassword = $currentPassword;
        $this->newPassword     = $newPassword;
    }
}
