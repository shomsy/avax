<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\Lifecycle;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;

final class InMemoryLifecycleStore implements LifecycleStoreInterface
{
    /** @var array<int, LifecycleRecord> */
    private array $records = [];

    public function save(LifecycleRecord $lifecycleRecord): void
    {
        $this->records[$lifecycleRecord->userId] = $lifecycleRecord;
    }

    public function find(UserId $userId): ?LifecycleRecord
    {
        return $this->records[$userId->value] ?? null;
    }
}
