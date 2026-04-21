<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Tenancy\AdminRealmSupport;

final class InMemoryAdminElevationStore implements AdminElevationStoreInterface
{
    /** @var array<string, AdminElevationRecord> */
    private array $records = [];

    public function start(AdminElevationRecord $record) : void
    {
        $this->records[$record->bindingId] = $record;
    }

    public function find(string $bindingId) : AdminElevationRecord|null
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
