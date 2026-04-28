<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Tenancy\AdminRealmSupport;

interface AdminElevationStoreInterface
{
    public function start(AdminElevationRecord $record) : void;

    public function find(string $bindingId) : AdminElevationRecord|null;

    public function revoke(string $bindingId) : void;

    public function revokeUser(int $userId) : void;
}
