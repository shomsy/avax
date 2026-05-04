<?php

declare(strict_types=1);

namespace Avax\Labs\API\DescribeApi\System\Flows\DescribeHttpContracts;

use Avax\Labs\API\DescribeApi\System\Capabilities\ReadApiDescriptions\ReadApiDescriptions;
use Avax\Labs\API\DescribeApi\System\PublicSurface\ApiContract;

final class DescribeHttpContracts
{
    public function __construct(private readonly ReadApiDescriptions $apiContracts)
    {
    }

    public function describe(): ApiContract
    {
        return new ApiContract($this->apiContracts->getAllEndpoints());
    }
}
