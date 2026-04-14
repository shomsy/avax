<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Scim\MarkOutage;

final readonly class MarkScimDirectoryOutageData
{
    public function __construct(
        public string $directoryId,
        public string|null $reason = null
    ) {}
}
