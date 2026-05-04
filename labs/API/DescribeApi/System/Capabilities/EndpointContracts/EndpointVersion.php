<?php

declare(strict_types=1);

namespace Avax\Labs\API\DescribeApi\System\Capabilities\EndpointContracts;

enum EndpointVersion: string
{
    case V1 = 'v1';
    case V2 = 'v2';
    case V3 = 'v3';

    public function label(): string
    {
        return $this->value;
    }

    public function isLatest(): bool
    {
        return self::V3 === $this;
    }
}