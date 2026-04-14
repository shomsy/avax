<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Lifecycle;

use Avax\Auth\System\Capability\User\UserId;

interface LifecycleStoreInterface
{
    public function save(LifecycleRecord $record) : void;

    public function find(UserId $userId) : LifecycleRecord|null;
}
