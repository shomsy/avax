<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Security\System\Configuration;

final readonly class HttpSecurityConfiguration
{
    public function __construct(
        public bool   $csrfEnabled = true,
        public string $csrfTokenName = '_token',
        public int    $csrfTokenLength = 40,
        public int    $csrfTokenLifetime = 7200,
        public bool   $securityHeadersStrict = true,
        public int    $signedUrlLifetime = 3600,
    ) {}
}
