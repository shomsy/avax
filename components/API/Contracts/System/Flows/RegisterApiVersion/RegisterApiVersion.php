<?php

declare(strict_types=1);

namespace Avax\Components\API\Contracts\System\Flows\RegisterApiVersion;

use Avax\Components\API\Contracts\System\Capabilities\Versioning\ApiVersion;
use Avax\Components\API\Contracts\System\Configuration\ApiContractsConfiguration;

final readonly class RegisterApiVersion
{
    public function __construct(
        private ApiContractsConfiguration $config = new ApiContractsConfiguration(),
    ) {}

    /**
     * @return array{version:string,major:string,minor:string,patch:string,current:string,is_major_change:bool,is_compatible:bool}
     */
    public function execute(string $version) : array
    {
        $apiVersion = ApiVersion::fromString(version: $version);

        return [
            'version'         => $apiVersion->toString(),
            'major'           => $apiVersion->major,
            'minor'           => $apiVersion->minor,
            'patch'           => $apiVersion->patch,
            'current'         => $this->config->currentVersion,
            'is_major_change' => $apiVersion->isMajorChange(other: ApiVersion::fromString(version: $this->config->currentVersion)),
            'is_compatible'   => $apiVersion->isCompatible(other: ApiVersion::fromString(version: $this->config->currentVersion)),
        ];
    }
}
