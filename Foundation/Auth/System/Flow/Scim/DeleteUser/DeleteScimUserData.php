<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Scim\DeleteUser;

use SensitiveParameter;

final readonly class DeleteScimUserData
{
    public function __construct(
        public string $directoryId,
        #[SensitiveParameter] public string $directoryToken,
        public string $externalId
    ) {}
}
