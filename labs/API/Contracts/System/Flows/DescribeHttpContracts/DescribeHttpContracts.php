<?php

declare(strict_types=1);

namespace Avax\Labs\API\Contracts\System\Flows\DescribeHttpContracts;

use Avax\Labs\API\Contracts\System\Capabilities\Adapters\ApiContractsAdapterInterface;
use Avax\Labs\API\Contracts\System\PublicSurface\ApiContract;

final class DescribeHttpContracts
{
    public function __construct(private readonly ApiContractsAdapterInterface $apiContracts)
    {
    }

    public function describe(): ApiContract
    {
        return new ApiContract($this->apiContracts->getAllEndpoints());
    }
}
