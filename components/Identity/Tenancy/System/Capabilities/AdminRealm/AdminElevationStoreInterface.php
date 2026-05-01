<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealm;

interface AdminElevationStoreInterface
{
    public function start(AdminElevationRecord $record): void;

    public function find(string $bindingId): ?AdminElevationRecord;

    public function revoke(string $bindingId): void;

    public function revokeUser(int $userId): void;
}
