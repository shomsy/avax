<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\System\Capabilities\OpenIDConnect\Runtime\ValidateRequestObject;

use SensitiveParameter;

final readonly class ValidateRequestObjectData
{
    public function __construct(
        #[SensitiveParameter]
        public string $requestUri,
    ) {}
}
