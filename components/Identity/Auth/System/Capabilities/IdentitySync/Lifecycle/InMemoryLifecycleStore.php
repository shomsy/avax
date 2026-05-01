<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\Lifecycle;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;

final class InMemoryLifecycleStore implements LifecycleStoreInterface
{
    /** @var array<int, LifecycleRecord> */
    private array $records = [];

    public function save(LifecycleRecord $record): void
    {
        $this->records[$record->userId] = $record;
    }

    public function find(UserId $userId): ?LifecycleRecord
    {
        return $this->records[$userId->value] ?? null;
    }
}
