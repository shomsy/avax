<?php

declare(strict_types=1);

namespace Avax\Labs\API\DescribeApi\System\Capabilities\AuthContracts;

final class RequiredPermission
{
    public function __construct(
        public readonly string $name,
        public readonly string $description,
        public readonly ?string $resource,
    ) {
    }

    public function matches(string $permission): bool
    {
        return $this->name === $permission;
    }
}
