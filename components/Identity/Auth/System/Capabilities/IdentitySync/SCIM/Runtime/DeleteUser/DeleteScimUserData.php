<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\DeleteUser;

use SensitiveParameter;

final readonly class DeleteScimUserData
{
    public function __construct(
        public string $directoryId,
        #[SensitiveParameter]
        public string $directoryToken,
        public string $externalId,
    ) {
    }
}
