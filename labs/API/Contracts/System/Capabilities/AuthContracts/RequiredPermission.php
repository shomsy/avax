<?php

declare(strict_types=1);

namespace Avax\Labs\API\Contracts\System\Capabilities\AuthContracts;

final class RequiredPermission
{
    public function __construct(
        public readonly string      $name,
        public readonly string      $description,
        public readonly string|null $resource,
    )
    {
    }

    public function matches(string $permission): bool
    {
        return $this->name === $permission;
    }
}
