<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Flows\ResolveTenant;

final readonly class ResolveTenant
{
    /**
     * @param array<string, mixed> $tenants
     *
     * @return array<string, mixed>|null
     */
    public function resolve(string $identifier, array $tenants) : ?array
    {
        return $tenants[$identifier] ?? null;
    }
}
