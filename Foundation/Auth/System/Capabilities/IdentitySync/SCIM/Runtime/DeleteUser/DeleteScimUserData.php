<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Scim\DeleteUser;

use SensitiveParameter;

final readonly class DeleteScimUserData
{
    public string $externalId;
    public string $directoryToken;
    public string $directoryId;

    public function __construct(
        string                       $directoryId,
        #[SensitiveParameter] string $directoryToken,
        string                       $externalId
    )
    {
        $this->directoryId    = $directoryId;
        $this->directoryToken = $directoryToken;
        $this->externalId     = $externalId;
    }
}
