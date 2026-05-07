<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\System\Capabilities\IdentitySync\SCIM\Runtime\RegisterDirectory;

final readonly class RegisterScimDirectoryData
{
    /**
     * @param array<string, list<string>> $groupRoleMap
     */
    public function __construct(public string $tenantSlug, public string $name, public array $groupRoleMap = []) {}
}
