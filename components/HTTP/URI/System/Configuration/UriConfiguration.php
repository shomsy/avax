<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\URI\System\Configuration;

final readonly class UriConfiguration
{
    public function __construct(
        public string $defaultScheme = 'https',
        public string $defaultHost = 'localhost',
        public bool   $strictValidation = false,
    ) {}
}
