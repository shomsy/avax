<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Scim\RegisterDirectory;

final readonly class RegisterScimDirectoryData
{
    /** @var array<string, list<string>> */
    public array  $groupRoleMap;
    public string $name;
    public string $tenantSlug;

    /**
     * @param array<string, list<string>> $groupRoleMap
     */
    public function __construct(
        string $tenantSlug,
        string $name,
        array  $groupRoleMap = []
    )
    {
        $this->tenantSlug   = $tenantSlug;
        $this->name         = $name;
        $this->groupRoleMap = $groupRoleMap;
    }
}
