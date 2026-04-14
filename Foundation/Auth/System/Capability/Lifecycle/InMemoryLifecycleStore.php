<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Lifecycle;

use Avax\Auth\System\Capability\User\UserId;

final class InMemoryLifecycleStore implements LifecycleStoreInterface
{
    /** @var array<int, LifecycleRecord> */
    private array $records = [];

    public function save(LifecycleRecord $record) : void
    {
        $this->records[$record->userId] = $record;
    }

    public function find(UserId $userId) : LifecycleRecord|null
    {
        return $this->records[$userId->value] ?? null;
    }
}
