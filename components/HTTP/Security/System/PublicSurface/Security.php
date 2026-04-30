<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Security\System\PublicSurface;

use Avax\Components\HTTP\Security\System\Capabilities\Csrf\CsrfTokens;

final readonly class Security
{
    public function __construct(
        private CsrfTokens $csrfTokens,
    ) {
    }

    public function csrfToken() : string
    {
        return $this->csrfTokens->getToken();
    }

    public function validateCsrfToken(string|null $token) : bool
    {
        return $this->csrfTokens->validateToken($token);
    }
}
