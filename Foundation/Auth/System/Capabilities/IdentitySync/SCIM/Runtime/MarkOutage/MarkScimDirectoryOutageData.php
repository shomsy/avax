<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\MarkOutage;

final readonly class MarkScimDirectoryOutageData
{
    public string|null $reason;
    public string      $directoryId;

    public function __construct(
        string      $directoryId,
        string|null $reason = null
    )
    {
        $this->directoryId = $directoryId;
        $this->reason      = $reason;
    }
}
