<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\IdentitySync\Lifecycle;

use Avax\Auth\System\Capabilities\Identity\User\UserId;

interface LifecycleStoreInterface
{
    public function save(LifecycleRecord $record) : void;

    public function find(UserId $userId) : LifecycleRecord|null;
}
