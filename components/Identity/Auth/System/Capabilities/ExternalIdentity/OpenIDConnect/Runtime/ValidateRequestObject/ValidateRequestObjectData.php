<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\ValidateRequestObject;

use SensitiveParameter;

final readonly class ValidateRequestObjectData
{
    public function __construct(
        #[SensitiveParameter] public string $requestUri
    ) {}
}
