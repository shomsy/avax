<?php

declare(strict_types=1);

namespace Avax\Labs\API\Contracts\System\Capabilities\Adapters;

use Avax\Labs\API\Contracts\System\Capabilities\EndpointContracts\EndpointContract;
use Avax\Labs\API\Contracts\System\Capabilities\EndpointContracts\EndpointVersion;

interface ApiContractsAdapterInterface
{
    public function registerEndpoint(EndpointContract $endpoint): void;

    public function findEndpoint(string $path, string $method): EndpointContract|null;

    /**
     * @return list<EndpointContract>
     */
    public function getAllEndpoints(): array;

    /**
     * @return list<EndpointContract>
     */
    public function getEndpointsByVersion(EndpointVersion $version): array;
}
