<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\ReadUserInfo;

final readonly class OidcUserInfo
{
    /**
     * @param array<string, bool|int|string> $claims
     */
    public function __construct(public array $claims) {}
}
