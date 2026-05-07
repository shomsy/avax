<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\JarmResponse;

use SensitiveParameter;

final readonly class BuildJarmResponseData
{
    public function __construct(
        public string  $clientId,
        #[SensitiveParameter]
        public string  $code,
        public ?string $state = null,
    ) {}
}
