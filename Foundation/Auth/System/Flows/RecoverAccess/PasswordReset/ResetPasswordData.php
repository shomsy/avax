<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\RecoverAccess\PasswordReset;

use SensitiveParameter;

/**
 * Boundary input for completing password recovery.
 */
final readonly class ResetPasswordData
{
    public string|null $userAgent;
    public string|null $ipAddress;
    public string      $newPassword;
    public string      $token;

    public function __construct(
        #[SensitiveParameter] string      $token,
        #[SensitiveParameter] string      $newPassword,
        #[SensitiveParameter] string|null $ipAddress = null,
        string|null                       $userAgent = null
    )
    {
        $this->token       = $token;
        $this->newPassword = $newPassword;
        $this->ipAddress   = $ipAddress;
        $this->userAgent   = $userAgent;
    }
}
