<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealm;

final class InMemoryAdminElevationStore implements AdminElevationStoreInterface
{
    /** @var array<string, AdminElevationRecord> */
    private array $records = [];

    public function start(AdminElevationRecord $adminElevationRecord) : void
    {
        $this->records[$adminElevationRecord->bindingId] = $adminElevationRecord;
    }

    public function find(string $bindingId) : ?AdminElevationRecord
    {
        return $this->records[$bindingId] ?? null;
    }

    public function revoke(string $bindingId) : void
    {
        unset($this->records[$bindingId]);
    }

    public function revokeUser(int $userId) : void
    {
        foreach ($this->records as $bindingId => $record) {
            if ($record->userId === $userId) {
                unset($this->records[$bindingId]);
            }
        }
    }
}
