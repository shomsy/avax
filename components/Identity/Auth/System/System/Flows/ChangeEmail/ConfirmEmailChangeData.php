<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\System\Flows\ChangeEmail;

use SensitiveParameter;

final readonly class ConfirmEmailChangeData
{
    public function __construct(
        #[SensitiveParameter]
        public string  $token,
        #[SensitiveParameter]
        public ?string $ipAddress = null,
        public ?string $userAgent = null,
    ) {}
}
