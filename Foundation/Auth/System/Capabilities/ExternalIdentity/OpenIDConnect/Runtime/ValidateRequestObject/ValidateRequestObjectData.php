<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\ValidateRequestObject;

use SensitiveParameter;

final readonly class ValidateRequestObjectData
{
    public string $requestUri;

    public function __construct(
        #[SensitiveParameter] string $requestUri
    )
    {
        $this->requestUri = $requestUri;
    }
}
