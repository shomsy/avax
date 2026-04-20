<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Scim\ReadGroups;

use SensitiveParameter;

final readonly class ScimGroupMember
{
    public string $email;
    public string $username;
    public int    $userId;
    public string $externalId;

    public function __construct(
        string                       $externalId,
        int                          $userId,
        string                       $username,
        #[SensitiveParameter] string $email
    )
    {
        $this->externalId = $externalId;
        $this->userId     = $userId;
        $this->username   = $username;
        $this->email      = $email;
    }
}
