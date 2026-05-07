<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Flows\SwitchTenant;

final readonly class SwitchTenant
{
    /**
     * @param array<string, mixed> $tenants
     *
     * @return array{success: bool, tenant: array<string, mixed>|null}
     */
    public function switch(string $identifier, array $tenants) : array
    {
        $tenant = $tenants[$identifier] ?? null;

        return [
            'success' => $tenant !== null,
            'tenant'  => $tenant,
        ];
    }
}
