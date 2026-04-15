<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\ChangeEmail;

use SensitiveParameter;

final readonly class BeginEmailChangeData
{
    public string|null $userAgent;
    public string|null $ipAddress;
    public string      $currentPassword;
    public string      $newEmail;

    public function __construct(
        #[SensitiveParameter] string      $newEmail,
        #[SensitiveParameter] string      $currentPassword,
        #[SensitiveParameter] string|null $ipAddress = null,
        string|null                       $userAgent = null
    )
    {
        $this->newEmail        = $newEmail;
        $this->currentPassword = $currentPassword;
        $this->ipAddress       = $ipAddress;
        $this->userAgent       = $userAgent;
    }
}
