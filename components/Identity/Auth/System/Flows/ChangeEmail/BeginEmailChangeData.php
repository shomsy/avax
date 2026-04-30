<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Flows\ChangeEmail;

use SensitiveParameter;

final readonly class BeginEmailChangeData
{
    public function __construct(
        #[SensitiveParameter]
        public string      $newEmail,
        #[SensitiveParameter]
        public string      $currentPassword,
        #[SensitiveParameter]
        public string|null $ipAddress = null,
        public string|null $userAgent = null,
    ) {}
}
