<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Oidc\ValidateRequestObject;

use SensitiveParameter;

final readonly class ValidateRequestObjectData
{
    public function __construct(
        #[SensitiveParameter] public string $requestUri
    ) {}
}
