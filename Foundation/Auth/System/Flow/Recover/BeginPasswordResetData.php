<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Recover;

use SensitiveParameter;

/**
 * Boundary input for starting password recovery.
 */
final readonly class BeginPasswordResetData
{
    public string|null $userAgent;
    public string|null $ipAddress;
    public string      $email;

    public function __construct(
        #[SensitiveParameter] string      $email,
        #[SensitiveParameter] string|null $ipAddress = null,
        string|null                       $userAgent = null
    )
    {
        $this->email     = $email;
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
    }
}
