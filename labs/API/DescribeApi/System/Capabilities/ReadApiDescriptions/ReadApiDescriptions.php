<?php

declare(strict_types=1);

namespace Avax\Labs\API\DescribeApi\System\Capabilities\ReadApiDescriptions;

use Avax\Labs\API\DescribeApi\System\Capabilities\EndpointContracts\EndpointContract;
use Avax\Labs\API\DescribeApi\System\Capabilities\EndpointContracts\EndpointVersion;

interface ReadApiDescriptions
{
    public function registerEndpoint(EndpointContract $endpoint): void;

    public function findEndpoint(string $path, string $method): ?EndpointContract;

    /**
     * @return list<EndpointContract>
     */
    public function getAllEndpoints(): array;

    /**
     * @return list<EndpointContract>
     */
    public function getEndpointsByVersion(EndpointVersion $version): array;
}
