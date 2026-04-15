<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Oidc\ReadUserInfo;

final readonly class OidcUserInfo
{
    public array $claims;

    /**
     * @param array<string, bool|int|string> $claims
     */
    public function __construct(
        array $claims
    )
    {
        $this->claims = $claims;
    }
}
