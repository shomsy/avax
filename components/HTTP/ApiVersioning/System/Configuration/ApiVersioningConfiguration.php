<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\ApiVersioning\System\Configuration;

/**
 * @phpstan-type SupportedVersions = list<string>
 */
final readonly class ApiVersioningConfiguration
{
    /**
     * @param SupportedVersions $supportedVersions
     */
    public function __construct(
        public array  $supportedVersions = ['v1'],
        public string $defaultVersion = 'v1',
        public string $headerName = 'X-API-Version',
    ) {}
}
