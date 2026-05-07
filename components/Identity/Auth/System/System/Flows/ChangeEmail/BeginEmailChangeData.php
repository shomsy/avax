<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\System\Flows\ChangeEmail;

use SensitiveParameter;

final readonly class BeginEmailChangeData
{
    public function __construct(
        #[SensitiveParameter]
        public string  $newEmail,
        #[SensitiveParameter]
        public string  $currentPassword,
        #[SensitiveParameter]
        public ?string $ipAddress = null,
        public ?string $userAgent = null,
    ) {}
}
