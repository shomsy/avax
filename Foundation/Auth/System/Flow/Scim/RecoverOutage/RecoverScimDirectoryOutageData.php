<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Scim\RecoverOutage;

final readonly class RecoverScimDirectoryOutageData
{
    public function __construct(
        public string $directoryId
    ) {}
}
