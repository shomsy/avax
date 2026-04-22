<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\RecoverOutage;

final readonly class RecoverScimDirectoryOutageData
{
    public function __construct(public string $directoryId)
    {
    }
}
