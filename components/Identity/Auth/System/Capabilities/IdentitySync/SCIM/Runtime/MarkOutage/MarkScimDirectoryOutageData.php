<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\MarkOutage;

final readonly class MarkScimDirectoryOutageData
{
    public function __construct(public string $directoryId, public string|null $reason = null) {}
}
