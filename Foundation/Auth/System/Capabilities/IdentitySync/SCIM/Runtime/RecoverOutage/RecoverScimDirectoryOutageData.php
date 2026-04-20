<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Scim\RecoverOutage;

final readonly class RecoverScimDirectoryOutageData
{
    public string $directoryId;

    public function __construct(
        string $directoryId
    )
    {
        $this->directoryId = $directoryId;
    }
}
