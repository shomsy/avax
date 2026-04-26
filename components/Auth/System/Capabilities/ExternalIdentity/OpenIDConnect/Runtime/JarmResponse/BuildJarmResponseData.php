<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\JarmResponse;

use SensitiveParameter;

final readonly class BuildJarmResponseData
{
    public function __construct(
        public string                       $clientId,
        #[SensitiveParameter] public string $code,
        public string|null                  $state = null
    ) {}
}
