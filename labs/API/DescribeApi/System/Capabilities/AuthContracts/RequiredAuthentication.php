<?php

declare(strict_types=1);

namespace Avax\Labs\API\DescribeApi\System\Capabilities\AuthContracts;

final class RequiredAuthentication
{
    /**
     * @param list<string> $scopes
     */
    public function __construct(
        public readonly string      $type,
        public readonly string|null $realm,
        public readonly array       $scopes = [],
    )
    {
    }

    public function isBearer(): bool
    {
        return $this->type === 'bearer';
    }

    public function isBasic(): bool
    {
        return $this->type === 'basic';
    }

    public function isApiKey(): bool
    {
        return $this->type === 'api_key';
    }
}
